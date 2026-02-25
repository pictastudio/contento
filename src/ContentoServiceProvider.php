<?php

namespace PictaStudio\Contento;

use Illuminate\Database\Eloquent\Relations\Relation;
use PictaStudio\Contento\Commands\InstallCommand;
use PictaStudio\Contento\Models\{Faq, FaqCategory, MailForm, Modal, Page, Setting};
use Spatie\LaravelPackageTools\{Package, PackageServiceProvider};

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
                'create_pages_table',
                'create_faq_tables',
                'create_mail_forms_table',
                'create_modals_table',
                'create_settings_table',
            ])
            ->hasRoute('api')
            ->hasCommands(InstallCommand::class);
    }

    public function packageBooted(): void
    {
        $this->registerPublishableAssets();
        $this->regiserMorphMap();
    }

    protected function registerPublishableAssets(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            $this->package->basePath('/../bruno/contento') => base_path('bruno/contento'),
        ], 'contento-bruno');
    }

    protected function regiserMorphMap(): void
    {
        Relation::morphMap([
            'page' => Page::class,
            'faq_category' => FaqCategory::class,
            'faq' => Faq::class,
            'mail_form' => MailForm::class,
            'modal' => Modal::class,
            'setting' => Setting::class,
        ]);
    }
}
