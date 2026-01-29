<?php

namespace PictaStudio\Contento;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use PictaStudio\Contento\Commands\ContentoCommand;

class ContentoServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('contento')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_contento_table')
            ->hasCommand(ContentoCommand::class);
    }
}
