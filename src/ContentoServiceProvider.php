<?php

namespace PictaStudio\Contento;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Application;
use Illuminate\Http\Resources\Json\JsonResource;
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
                'create_faq_tables',
                'create_mail_forms_table',
                'create_modals_table',
                'create_content_tags_table',
                'create_content_taggables_table',
                'create_settings_table',
                'seed_contento_data',
            ])
            ->hasCommands(InstallCommand::class);
    }

    public function registeringPackage(): void
    {
        $this->app->bind('contento', fn (Application $app) => (
            $app->make(Contento::class)
        ));
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

        if (!config('contento.routes.api.json_resource_enable_wrapping', true)) {
            JsonResource::withoutWrapping();
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
