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
            ->hasMigrations([
                'create_contento_pages_table',
                'create_contento_faq_tables',
                'create_contento_mail_forms_table',
                'create_contento_modals_table',
                'create_contento_settings_table',
            ])
            ->hasRoute('api')
            ->hasCommand(ContentoCommand::class);
    }
}
