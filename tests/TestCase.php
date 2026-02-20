<?php

namespace PictaStudio\Contento\Tests;

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use PictaStudio\Contento\ContentoServiceProvider;
use PictaStudio\Translatable\Locales;
use PictaStudio\Translatable\Middleware\SetLocaleFromHeader;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'PictaStudio\\Contento\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );

        $this->loadMigrationsFrom(__DIR__ . '/../vendor/pictastudio/translatable/database/migrations');
        $this->registerTranslatableBindings();
        $this->registerLocaleMiddleware();

        app()->setLocale('en');
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('translatable.locales', ['en', 'it']);
        config()->set('translatable.fallback_locale', 'en');

        $migrationFiles = [
            'create_pages_table.php',
            'create_faq_tables.php',
            'create_mail_forms_table.php',
            'create_modals_table.php',
            'create_settings_table.php',
        ];

        foreach ($migrationFiles as $file) {
            $migration = include __DIR__ . '/../database/migrations/' . $file;
            $migration->up();
        }
    }

    protected function getPackageProviders($app): array
    {
        return [
            ContentoServiceProvider::class,
        ];
    }

    protected function registerTranslatableBindings(): void
    {
        $this->app->singleton(Locales::class);
        $this->app->alias(Locales::class, 'translatable.locales');
        $this->app->alias(Locales::class, 'translatable');
    }

    protected function registerLocaleMiddleware(): void
    {
        if (!(bool) config('translatable.register_locale_middleware', true)) {
            return;
        }

        if (!$this->app->bound(HttpKernel::class)) {
            return;
        }

        $kernel = $this->app->make(HttpKernel::class);

        if (!method_exists($kernel, 'prependMiddleware')) {
            return;
        }

        if (method_exists($kernel, 'hasMiddleware') && $kernel->hasMiddleware(SetLocaleFromHeader::class)) {
            return;
        }

        $kernel->prependMiddleware(SetLocaleFromHeader::class);
    }
}
