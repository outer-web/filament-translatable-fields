<?php

declare(strict_types=1);

namespace Outerweb\FilamentTranslatableFields;

use Arr;
use Closure;
use Filament\Contracts\Plugin;
use Filament\Forms\Components\Field;
use Filament\Panel;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class TranslatableFieldsPlugin implements Plugin
{
    use EvaluatesClosures;

    protected array|Closure $supportedLocales = [];

    protected string|Closure|null $defaultLocale = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'outerweb-filament-translatable-fields';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        Field::macro(
            'translatable',
            function (
                bool|Closure $translatable = true,
                ?Closure $modifyLocalizedFieldUsing = null,
                array|Closure|null $supportedLocales = null,
                string|Closure|null $defaultLocale = null,
            ): Field|Tabs {
                /** @var Field $this */
                // @phpstan-ignore varTag.nativeType
                if (! $this->evaluate($translatable)) {
                    return $this;
                }

                $supportedLocales = $this->evaluate($supportedLocales)
                    ?? TranslatableFieldsPlugin::get()->getSupportedLocales();

                if (! Arr::isAssoc($supportedLocales)) {
                    $supportedLocales = array_combine($supportedLocales, array_map(fn ($item) => Str::title($item), $supportedLocales));
                }

                $defaultLocale = $this->evaluate($defaultLocale)
                    ?? TranslatableFieldsPlugin::get()->getDefaultLocale();

                return Tabs::make()
                    ->tabs(
                        collect($supportedLocales)
                            ->map(function (string $label, string $locale) use ($modifyLocalizedFieldUsing): Tab {
                                /** @var Field $this */
                                $field = $this->getClone()
                                    ->name("{$this->getName()}.{$locale}")
                                    ->label($this->getLabel())
                                    ->statePath("{$this->getStatePath(false)}.{$locale}");

                                $field = $field->evaluate($modifyLocalizedFieldUsing, [
                                    'field' => $field,
                                    'locale' => $locale,
                                ]) ?? $field;

                                return Tab::make($this->getName())
                                    ->label($label)
                                    ->schema([$field])
                                    ->extraAttributes([
                                        'x-tooltip' => '$el.hasAttribute(\'data-tab-key\')
                                                ? {
                                                    content: \''.__('filament-translatable-fields::translations.tooltips.switch_locale', ['locale' => $label]).'\',
                                                    theme: $store.theme,
                                                    allowHTML: true,
                                                }
                                                : \'\'
                                        ',
                                    ]);
                            })
                            ->all(),
                    )
                    ->activeTab((array_search($defaultLocale, array_keys($supportedLocales), true) ?: 0) + 1)
                    ->columnSpan($this->getColumnSpan())
                    ->extraAlpineAttributes([
                        'x-on:click' => 'if ($event.shiftKey) { $dispatch(\'filament-translatable-fields:change-locale\', { tab: tab }) }',
                        'x-on:filament-translatable-fields:change-locale.window' => 'tab = $event.detail.tab',
                    ], true);
            }
        );

        Component::macro(
            'translatable',
            function (
                bool|Closure $translatable = true,
                ?Closure $modifyLocalizedFieldUsing = null,
                array|Closure|null $supportedLocales = null,
                string|Closure|null $defaultLocale = null,
            ): Component {
                /** @var Component $this */
                // @phpstan-ignore varTag.nativeType
                if (! $this->evaluate($translatable)) {
                    return $this;
                }

                $supportedLocales = $this->evaluate($supportedLocales)
                    ?? TranslatableFieldsPlugin::get()->getSupportedLocales();

                if (! Arr::isAssoc($supportedLocales)) {
                    $supportedLocales = array_combine($supportedLocales, array_map(fn ($item) => Str::title($item), $supportedLocales));
                }

                $defaultLocale = $this->evaluate($defaultLocale)
                    ?? TranslatableFieldsPlugin::get()->getDefaultLocale();

                $childComponents = $this->childComponents['default'] ?? [];

                return $this
                    ->columnSpanFull()
                    ->childComponents([
                        Tabs::make()
                            ->tabs(function () use ($supportedLocales, $modifyLocalizedFieldUsing, $childComponents): array {
                                return collect($supportedLocales)
                                    ->map(function (string $label, string $locale) use ($modifyLocalizedFieldUsing, $childComponents): Tab {
                                        $childComponents = collect($childComponents)
                                            ->map(function (Component $component) use ($locale, $modifyLocalizedFieldUsing): Component {
                                                if (! $component instanceof Field) {
                                                    return $component;
                                                }

                                                $clonedField = $component->getClone()
                                                    ->name("{$component->getName()}.{$locale}")
                                                    ->label($component->getLabel())
                                                    ->statePath("{$component->getStatePath(false)}.{$locale}");

                                                $clonedField = $this->evaluate($modifyLocalizedFieldUsing, [
                                                    'component' => $clonedField,
                                                    'locale' => $locale,
                                                ]) ?? $clonedField;

                                                return $clonedField;
                                            })
                                            ->all();

                                        /** @var Component $this */
                                        return Tab::make()
                                            ->label($label)
                                            ->schema($childComponents)
                                            ->extraAttributes([
                                                'x-tooltip' => '$el.hasAttribute(\'data-tab-key\')
                                                ? {
                                                    content: \''.__('filament-translatable-fields::translations.tooltips.switch_locale', ['locale' => $label]).'\',
                                                    theme: $store.theme,
                                                    allowHTML: true,
                                                }
                                                : \'\'
                                        ',
                                            ]);
                                    })
                                    ->all();
                            })
                            ->activeTab((array_search($defaultLocale, array_keys($supportedLocales), true) ?: 0) + 1)
                            ->columns($this->getColumns())
                            ->columnOrder($this->getColumnOrder())
                            ->columnStart($this->getColumnStart())
                            ->columnSpan($this->getColumnSpan())
                            ->extraAlpineAttributes([
                                'x-on:click' => 'if ($event.shiftKey) { $dispatch(\'filament-translatable-fields:change-locale\', { tab: tab }) }',
                                'x-on:filament-translatable-fields:change-locale.window' => 'tab = $event.detail.tab',
                            ], true),
                    ]);
            }
        );
    }

    public function supportedLocales(array|Closure $locales): static
    {
        $this->supportedLocales = $locales;

        return $this;
    }

    public function getSupportedLocales(): array
    {
        $array = $this->evaluate($this->supportedLocales);

        if (empty($array)) {
            $array = Config::has('app.supported_locales')
            ? Config::array('app.supported_locales')
            : array_unique([
                Config::string('app.locale'),
                Config::string('app.fallback_locale'),
            ]);
        }

        if (! Arr::isAssoc($array)) {
            $array = array_combine($array, array_map(fn ($item) => Str::upper($item), $array));
        }

        return $array;
    }

    public function defaultLocale(string|Closure $locale): static
    {
        $this->defaultLocale = $locale;

        return $this;
    }

    public function getDefaultLocale(): string
    {
        return $this->evaluate($this->defaultLocale) ?? Config::string('app.locale');
    }
}
