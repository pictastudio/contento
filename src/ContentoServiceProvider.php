<?php

namespace PictaStudio\Contento;

use Illuminate\Support\Facades\Gate;
use PictaStudio\Contento\Commands\ContentoCommand;
use PictaStudio\Contento\Models\{Faq, FaqCategory, MailForm, Modal, Page, Setting};
use PictaStudio\Contento\Policies\{FaqCategoryPolicy, FaqPolicy, MailFormPolicy, ModalPolicy, PagePolicy, SettingPolicy};
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
                'create_contento_pages_table',
                'create_contento_faq_tables',
                'create_contento_mail_forms_table',
                'create_contento_modals_table',
                'create_contento_settings_table',
            ])
            ->hasRoute('api')
            ->hasCommand(ContentoCommand::class);
    }

    public function packageBooted(): void
    {
        $this->registerPolicies();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(FaqCategory::class, FaqCategoryPolicy::class);
        Gate::policy(Faq::class, FaqPolicy::class);
        Gate::policy(MailForm::class, MailFormPolicy::class);
        Gate::policy(Modal::class, ModalPolicy::class);
        Gate::policy(Setting::class, SettingPolicy::class);
    }
}
