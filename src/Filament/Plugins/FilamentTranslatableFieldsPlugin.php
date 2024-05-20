<?php

namespace Outerweb\FilamentTranslatableFields\Filament\Plugins;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Tabs;
use Filament\Panel;

class FilamentTranslatableFieldsPlugin implements Plugin
{
    protected array|Closure $supportedLocales = [];

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'outerweb-filament-translatable-fields';
    }

    public function supportedLocales(array|Closure $supportedLocales): static
    {
        $this->supportedLocales = $supportedLocales;

        return $this;
    }

    public function getSupportedLocales(): array
    {
        $locales = is_callable($this->supportedLocales) ? call_user_func($this->supportedLocales) : $this->supportedLocales;

        if (empty($locales)) {
            $locales[] = config('app.locale');
        }

        return $locales;
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        $supportedLocales = $this->getSupportedLocales();

        Field::macro('translatable', function (bool $translatable = true, ?array $customLocales = null) use ($supportedLocales) {
            if (!$translatable) {
                return $this;
            }

            /**
             * @var Field $field
             * @var Field $this
             */
            $field = $this->getClone();

            $tabs = collect($customLocales ?? $supportedLocales)
                ->map(function ($label, $key) use ($field) {
                    $locale = is_string($key) ? $key : $label;

                    $clone = $field
                        ->getClone()
                        ->name("{$field->getName()}.{$locale}")
                        ->label($field->getLabel())
                        ->statePath("{$field->getStatePath(false)}.{$locale}");

                    // Apply rules specific to the locale
                    if (isset($this->localeRules[$locale])) {
                        $clone->rules($this->localeRules[$locale]);
                    }

                    return Tabs\Tab::make($locale)
                        ->label(is_string($key) ? $label : strtoupper($locale))
                        ->schema([$clone]);->statePath("{$field->getStatePath(false)}.{$locale}"),
                        ]);
                })
                ->toArray();

            $tabsField = Tabs::make('translations')
                ->tabs($tabs);

            return $tabsField;
        });

        Field::macro('rulesFor', function (array $localeRules) {
            $this->localeRules = $localeRules;

            return $this;
        });
    }
}
