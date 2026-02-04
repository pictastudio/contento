<?php

namespace PictaStudio\Contento\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use PictaStudio\Contento\ContentoServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'PictaStudio\\Contento\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        $migrationFiles = [
            'create_contento_pages_table.php',
            'create_contento_faq_tables.php',
            'create_contento_mail_forms_table.php',
            'create_contento_modals_table.php',
            'create_contento_settings_table.php',
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
}
