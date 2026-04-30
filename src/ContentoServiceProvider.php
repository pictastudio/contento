<?php

namespace PictaStudio\Contento;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use PictaStudio\Contento\Commands\InstallCommand;
use Spatie\LaravelPackageTools\{Package, PackageServiceProvider};

class ContentoServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('contento')
            ->hasConfigFile()
            ->hasMigrations([
                'create_pages_table',
                'update_pages_add_metadata',
                'create_menus_table',
                'create_menu_items_table',
                'update_menu_items_add_tree_path',
                'update_menu_items_add_sort_order',
                'create_faq_tables',
                'update_faqs_add_sort_order',
                'create_mail_forms_table',
                'create_modals_table',
                'create_content_tags_table',
                'update_content_tags_add_images',
                'create_content_taggables_table',
                'create_metadata_table',
                'create_settings_table',
                'create_catalog_images_table',
            ])
            ->hasCommands(InstallCommand::class);
    }

    public function registeringPackage(): void
    {
        $this->app->bind('contento', fn (Application $app) => (
            $app->make(Contento::class)
        ));
    }

    public function packageRegistered(): void
    {
        $this->mergeContentoConfig();
    }

    public function packageBooted(): void
    {
        $this->registerPublishableAssets();
        $this->registerApiRoutes();
        $this->bindValidationClasses();
        $this->registerMorphMap();
    }

    private function registerApiRoutes(): void
    {
        if (!config('contento.routes.api.enable', true)) {
            return;
        }

        $prefix = $this->apiPrefix();
        $name = mb_rtrim($this->apiName(), '.');

        Route::middleware($this->apiMiddleware())
            ->prefix($prefix)
            ->name($name === '' ? '' : $name . '.')
            ->group(fn () => (
                $this->loadRoutesFrom($this->package->basePath('/../routes/api.php'))
            ));
    }

    private function mergeContentoConfig(): void
    {
        $packageConfig = require dirname(__DIR__) . '/config/contento.php';
        $applicationConfig = config('contento', []);

        config()->set(
            'contento',
            $this->mergeConfigRecursively(
                $packageConfig,
                is_array($applicationConfig) ? $applicationConfig : []
            )
        );
    }

    /**
     * Merge associative config arrays recursively while preserving list overrides.
     *
     * @param  array<string, mixed>  $defaults
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function mergeConfigRecursively(array $defaults, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            if (
                array_key_exists($key, $defaults)
                && is_array($defaults[$key])
                && is_array($value)
                && !array_is_list($defaults[$key])
                && !array_is_list($value)
            ) {
                $defaults[$key] = $this->mergeConfigRecursively($defaults[$key], $value);

                continue;
            }

            $defaults[$key] = $value;
        }

        return $defaults;
    }

    private function bindValidationClasses(): void
    {
        $validations = config('contento.validations', []);

        foreach ($validations as $contract => $implementation) {
            $this->app->singleton($contract, $implementation);
        }
    }

    private function registerMorphMap(): void
    {
        $morphMap = collect(config('contento.models', []))
            ->filter(fn (mixed $model): bool => is_string($model) && class_exists($model))
            ->toArray();

        if ($morphMap !== []) {
            Relation::morphMap($morphMap);
        }
    }

    private function registerPublishableAssets(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            $this->package->basePath('/../bruno/contento') => base_path('bruno/contento'),
        ], 'contento-bruno');

        $this->publishes([
            $this->package->basePath('/../routes/api.php') => base_path('routes/contento-api.php'),
        ], 'contento-routes');

        $this->publishes([
            $this->package->basePath('/../database/migrations/seed_contento_data.php') => base_path(
                'database/migrations/' . date('Y_m_d_His') . '_seed_contento_data.php'
            ),
        ], 'contento-default-settings');
    }

    private function apiPrefix(): string
    {
        return (string) config('contento.routes.api.v1.prefix', 'api/contento/v1');
    }

    private function apiName(): string
    {
        return (string) config('contento.routes.api.v1.name', 'api.contento.v1');
    }

    /**
     * @return array<int, string>
     */
    private function apiMiddleware(): array
    {
        $middleware = config('contento.routes.api.v1.middleware', ['api']);

        if (!is_array($middleware)) {
            return ['api'];
        }

        return collect($middleware)
            ->filter(fn (mixed $item): bool => is_string($item) && $item !== '')
            ->values()
            ->all();
    }
}
