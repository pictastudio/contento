<?php

namespace PictaStudio\Contento\Tests;

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Nevadskiy\Tree\TreeServiceProvider;
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
        $connection = env('DB_CONNECTION', 'testing');
        $sqliteConnection = [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ];

        config()->set('database.default', $connection);

        if ($connection === 'mysql') {
            config()->set('database.connections.mysql', [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'contento_test'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'unix_socket' => env('DB_SOCKET', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => null,
            ]);
        } else {
            config()->set('database.connections.sqlite', $sqliteConnection);
            config()->set('database.connections.' . $connection, $sqliteConnection);
        }

        config()->set('translatable.locales', ['en', 'it']);
        config()->set('translatable.fallback_locale', 'en');

        $migrationFiles = [
            'create_pages_table.php',
            'update_pages_add_metadata.php',
            'create_menus_table.php',
            'create_menu_items_table.php',
            'update_menu_items_add_tree_path.php',
            'update_menu_items_add_sort_order.php',
            'create_faq_tables.php',
            'update_pages_and_faq_categories_make_abstract_nullable.php',
            'update_faqs_add_sort_order.php',
            'create_mail_forms_table.php',
            'create_modals_table.php',
            'create_content_tags_table.php',
            'update_content_tags_add_images.php',
            'create_content_taggables_table.php',
            'create_metadata_table.php',
            'create_settings_table.php',
            'create_catalog_images_table.php',
        ];

        foreach ($migrationFiles as $file) {
            $migration = include __DIR__ . '/../database/migrations/' . $file;
            $migration->up();
        }
    }

    protected function getPackageProviders($app): array
    {
        return [
            TreeServiceProvider::class,
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
