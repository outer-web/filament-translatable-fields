<?php

declare(strict_types=1);

namespace Outerweb\FilamentTranslatableFields;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentTranslatableFieldsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-translatable-fields';

    public static string $viewNamespace = 'filament-translatable-fields';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name);

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }
    }
}
