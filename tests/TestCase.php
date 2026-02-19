<?php

namespace PictaStudio\Contento\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use PictaStudio\Contento\ContentoServiceProvider;
use PictaStudio\Translatable\TranslatableServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'PictaStudio\\Contento\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );

        $this->loadMigrationsFrom(__DIR__ . '/../laravel-translatable/database/migrations');

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
            TranslatableServiceProvider::class,
            ContentoServiceProvider::class,
        ];
    }
}
