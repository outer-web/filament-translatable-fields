<?php

namespace Outerweb\FilamentTranslatableFields;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentTranslatableFieldsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-translatable-fields')
            ->hasConfigFile()
            ->hasInstallCommand(function (InstallCommand $command) {
                $command->publishConfigFile();

                if ($composerFile = file_get_contents(__DIR__ . '/../composer.json')) {
                    if ($githubRepo = json_decode($composerFile, true)['homepage'] ?? null) {
                        $command->askToStarRepoOnGitHub($githubRepo);
                    }
                }
            });
    }
}
