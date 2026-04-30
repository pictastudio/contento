<?php

use PictaStudio\Contento\Validations\{CatalogImageValidation, ContentTagValidation, FaqCategoryValidation, FaqValidation, MailFormValidation, MenuItemValidation, MenuValidation, MetadataValidation, ModalValidation, PageValidation, SettingValidation};
use PictaStudio\Contento\Validations\Contracts\{CatalogImageValidationRules, ContentTagValidationRules, FaqCategoryValidationRules, FaqValidationRules, MailFormValidationRules, MenuItemValidationRules, MenuValidationRules, MetadataValidationRules, ModalValidationRules, PageValidationRules, SettingValidationRules};

use function Pest\Laravel\getJson;
use function PictaStudio\Contento\Helpers\Functions\{get_fresh_model_instance, query, resolve_model};

it('resolves configured models through helper functions', function () {
    expect(resolve_model('page'))->toBe(config('contento.models.page'));
    expect(resolve_model('menu'))->toBe(config('contento.models.menu'));
    expect(resolve_model('menu_item'))->toBe(config('contento.models.menu_item'));
    expect(resolve_model('content_tag'))->toBe(config('contento.models.content_tag'));
    expect(resolve_model('metadata'))->toBe(config('contento.models.metadata'));
    expect(resolve_model('catalog_image'))->toBe(config('contento.models.catalog_image'));

    expect(query('page')->getModel())->toBeInstanceOf(resolve_model('page'));
    expect(query('menu')->getModel())->toBeInstanceOf(resolve_model('menu'));
    expect(query('metadata')->getModel())->toBeInstanceOf(resolve_model('metadata'));
    expect(query('catalog_image')->getModel())->toBeInstanceOf(resolve_model('catalog_image'));
    expect(get_fresh_model_instance('metadata'))->toBeInstanceOf(resolve_model('metadata'));
    expect(get_fresh_model_instance('setting'))->toBeInstanceOf(resolve_model('setting'));
    expect(get_fresh_model_instance('catalog_image'))->toBeInstanceOf(resolve_model('catalog_image'));
});

it('binds validation contracts from configuration', function () {
    expect(app(ContentTagValidationRules::class))->toBeInstanceOf(ContentTagValidation::class);
    expect(app(CatalogImageValidationRules::class))->toBeInstanceOf(CatalogImageValidation::class);
    expect(app(FaqCategoryValidationRules::class))->toBeInstanceOf(FaqCategoryValidation::class);
    expect(app(FaqValidationRules::class))->toBeInstanceOf(FaqValidation::class);
    expect(app(MailFormValidationRules::class))->toBeInstanceOf(MailFormValidation::class);
    expect(app(MetadataValidationRules::class))->toBeInstanceOf(MetadataValidation::class);
    expect(app(MenuValidationRules::class))->toBeInstanceOf(MenuValidation::class);
    expect(app(MenuItemValidationRules::class))->toBeInstanceOf(MenuItemValidation::class);
    expect(app(ModalValidationRules::class))->toBeInstanceOf(ModalValidation::class);
    expect(app(PageValidationRules::class))->toBeInstanceOf(PageValidation::class);
    expect(app(SettingValidationRules::class))->toBeInstanceOf(SettingValidation::class);
});

it('registers endpoints using the versioned api prefix config', function () {
    getJson(config('contento.routes.api.v1.prefix') . '/pages')
        ->assertOk();

    getJson(config('contento.routes.api.v1.prefix') . '/metadata')
        ->assertOk();

    getJson(config('contento.routes.api.v1.prefix') . '/catalog-images')
        ->assertOk();
});

it('registers the menu item tree path upgrade migration for publishing', function () {
    $provider = app()->getProvider(PictaStudio\Contento\ContentoServiceProvider::class);
    $packageProperty = new ReflectionProperty(Spatie\LaravelPackageTools\PackageServiceProvider::class, 'package');
    $packageProperty->setAccessible(true);

    $package = $packageProperty->getValue($provider);

    expect($package->migrationFileNames)
        ->toContain('create_menu_items_table')
        ->toContain('update_menu_items_add_tree_path')
        ->toContain('update_menu_items_add_sort_order')
        ->toContain('create_metadata_table')
        ->toContain('create_catalog_images_table')
        ->toContain('update_faqs_add_sort_order');
});

it('merges nested config defaults while preserving list overrides', function () {
    config()->set('contento', [
        'routes' => [
            'api' => [
                'v1' => [
                    'prefix' => 'api/custom/v1',
                    'middleware' => ['throttle:api'],
                ],
            ],
        ],
    ]);

    $provider = app()->getProvider(PictaStudio\Contento\ContentoServiceProvider::class);
    $provider->packageRegistered();

    expect(config('contento.routes.api.v1.prefix'))->toBe('api/custom/v1')
        ->and(config('contento.routes.api.v1.name'))->toBe('api.contento.v1')
        ->and(config('contento.routes.api.v1.pagination.per_page'))->toBe(15)
        ->and(config('contento.routes.api.v1.pagination.max_per_page'))->toBe(100)
        ->and(config('contento.routes.api.v1.middleware'))->toBe(['throttle:api']);
});
