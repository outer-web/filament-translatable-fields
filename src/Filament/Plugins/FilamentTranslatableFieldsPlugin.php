<?php

namespace Outerweb\FilamentTranslatableFields\Filament\Plugins;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Outerweb\FilamentTranslatableFields\Concerns\HasTranslatableFormsFieldMacros;

class FilamentTranslatableFieldsPlugin implements Plugin
{
    use HasTranslatableFormsFieldMacros;

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

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        $this->translatableFormsFieldMacros();
    }
}
