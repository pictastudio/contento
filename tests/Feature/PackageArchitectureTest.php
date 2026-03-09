<?php

use PictaStudio\Contento\Validations\{ContentTagValidation, FaqCategoryValidation, FaqValidation, MailFormValidation, ModalValidation, PageValidation, SettingValidation};
use PictaStudio\Contento\Validations\Contracts\{ContentTagValidationRules, FaqCategoryValidationRules, FaqValidationRules, MailFormValidationRules, ModalValidationRules, PageValidationRules, SettingValidationRules};

use function Pest\Laravel\getJson;
use function PictaStudio\Contento\Helpers\Functions\{get_fresh_model_instance, query, resolve_model};

it('resolves configured models through helper functions', function () {
    expect(resolve_model('page'))->toBe(config('contento.models.page'));
    expect(resolve_model('content_tag'))->toBe(config('contento.models.content_tag'));

    expect(query('page')->getModel())->toBeInstanceOf(resolve_model('page'));
    expect(get_fresh_model_instance('setting'))->toBeInstanceOf(resolve_model('setting'));
});

it('binds validation contracts from configuration', function () {
    expect(app(ContentTagValidationRules::class))->toBeInstanceOf(ContentTagValidation::class);
    expect(app(FaqCategoryValidationRules::class))->toBeInstanceOf(FaqCategoryValidation::class);
    expect(app(FaqValidationRules::class))->toBeInstanceOf(FaqValidation::class);
    expect(app(MailFormValidationRules::class))->toBeInstanceOf(MailFormValidation::class);
    expect(app(ModalValidationRules::class))->toBeInstanceOf(ModalValidation::class);
    expect(app(PageValidationRules::class))->toBeInstanceOf(PageValidation::class);
    expect(app(SettingValidationRules::class))->toBeInstanceOf(SettingValidation::class);
});

it('registers endpoints using the versioned api prefix config', function () {
    getJson(config('contento.routes.api.v1.prefix') . '/pages')
        ->assertOk();
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

    $provider = app()->getProvider(\PictaStudio\Contento\ContentoServiceProvider::class);
    $provider->packageRegistered();

    expect(config('contento.routes.api.v1.prefix'))->toBe('api/custom/v1')
        ->and(config('contento.routes.api.v1.name'))->toBe('api.contento.v1')
        ->and(config('contento.routes.api.v1.pagination.per_page'))->toBe(15)
        ->and(config('contento.routes.api.v1.pagination.max_per_page'))->toBe(100)
        ->and(config('contento.routes.api.v1.middleware'))->toBe(['throttle:api']);
});
