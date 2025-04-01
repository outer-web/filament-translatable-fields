<?php

namespace Outerweb\FilamentTranslatableFields\Filament\Plugins;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Forms;
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

        Forms\Components\Field::macro('translatable', function (
            bool $translatable = true,
            ?array $customLocales = null,
            ?array $localeSpecificRules = null
        ) use ($supportedLocales) {
            if (! $translatable) {
                return $this;
            }

            /**
             * @var Field $field
             * @var Field $this
             */
            $field = $this->getClone();
            $locales = collect($customLocales ?? $supportedLocales);

            // ? Disguise if it's only one locale If only one locale, adjust and return the cloned field directly.
            if (config('filament-translatable-fields.disguise_when_one_locale_available') && $locales->count() === 1) {
                $locale = $locales->first();
                $clone = $field
                    ->name("{$field->getName()}.{$locale}")
                    ->label($field->getLabel())
                    ->statePath("{$field->getStatePath(false)}.{$locale}");

                if ($localeSpecificRules && isset($localeSpecificRules[$locale])) {
                    $localeRules = is_callable($localeSpecificRules[$locale])
                        ? call_user_func($localeSpecificRules[$locale], $field)
                        : $localeSpecificRules[$locale];

                    $clone->rules([
                        ...$field->getRules(),
                        ...$localeRules,
                    ]);
                }

                return $clone;
            }

            // ? Otherwise, build a tab for each locale.
            $tabs = $locales->map(function ($label, $key) use ($field, $localeSpecificRules) {
                $locale = is_string($key) ? $key : $label;

                $clone = $field
                    ->getClone()
                    ->name("{$field->getName()}.{$locale}")
                    ->label($field->getLabel())
                    ->statePath("{$field->getStatePath(false)}.{$locale}");

                if ($localeSpecificRules && isset($localeSpecificRules[$locale])) {
                    $localeRules = is_callable($localeSpecificRules[$locale])
                        ? call_user_func($localeSpecificRules[$locale], $field)
                        : $localeSpecificRules[$locale];

                    $clone->rules([
                        ...$field->getRules(),
                        ...$localeRules,
                    ]);
                }

                return Forms\Components\Tabs\Tab::make($locale)
                    ->label(is_string($key) ? $label : strtoupper($locale))
                    ->schema([$clone]);
            })->toArray();

            return Forms\Components\Tabs::make('translations')->tabs($tabs);
        });
    }
}
