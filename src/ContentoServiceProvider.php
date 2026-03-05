<?php

namespace PictaStudio\Contento;

use Illuminate\Database\Eloquent\Relations\Relation;
use PictaStudio\Contento\Commands\InstallCommand;
use PictaStudio\Contento\Contracts\ContentTagValidationRules;
use PictaStudio\Contento\Models\{ContentTag, Faq, FaqCategory, MailForm, Modal, Page, Setting};
use PictaStudio\Contento\Validations\ContentTagValidation;
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
                'create_content_tags_table',
                'create_content_taggables_table',
                'create_settings_table',
                'seed_contento_data',
            ])
            ->hasRoute('api')
            ->hasCommands(InstallCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->bind(ContentTagValidationRules::class, ContentTagValidation::class);
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
            'content_tag' => ContentTag::class,
            'setting' => Setting::class,
        ]);
    }
}
