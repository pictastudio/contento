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

        app()->setLocale('en');
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('translatable.locales', ['en', 'it']);
        config()->set('translatable.fallback_locale', 'en');

        $migrationFiles = [
            'create_contento_pages_table.php',
            'create_contento_faq_tables.php',
            'create_contento_mail_forms_table.php',
            'create_contento_modals_table.php',
            'create_contento_settings_table.php',
            'create_contento_translations_table.php',
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
