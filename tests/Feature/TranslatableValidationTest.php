<?php

use PictaStudio\Contento\Models\{ContentTag, Faq, FaqCategory, MailForm, Menu, MenuItem, Modal, Page};
use PictaStudio\Translatable\Contracts\Translatable as TranslatableContract;

use function Pest\Laravel\{assertDatabaseHas, postJson, putJson};

dataset('translatable_models', [
    'page' => [Page::class, ['title', 'abstract', 'content', 'slug']],
    'menu' => [Menu::class, ['title', 'slug']],
    'menu-item' => [MenuItem::class, ['title', 'slug', 'link']],
    'faq-category' => [FaqCategory::class, ['title', 'abstract', 'slug']],
    'faq' => [Faq::class, ['title', 'content', 'slug']],
    'mail-form' => [MailForm::class, ['name', 'slug', 'custom_fields', 'redirect_url']],
    'modal' => [Modal::class, ['title', 'content', 'cta_button_text', 'cta_button_url', 'slug']],
    'content-tag' => [ContentTag::class, ['name', 'slug', 'abstract', 'description']],
]);

dataset('translatable_store_routes', [
    'page' => ['/pages', ['content' => ['body' => 'Body']]],
    'menu' => ['/menus', ['active' => true]],
    'menu-item' => ['/menu-items', ['menu_id' => null, 'active' => true]],
    'faq-category' => ['/faq-categories', ['active' => true]],
    'faq' => ['/faqs', ['content' => 'Answer']],
    'mail-form' => ['/mail-forms', ['email_to' => 'hello@example.com']],
    'modal' => ['/modals', ['content' => 'Body', 'timeout' => 10]],
    'content-tag' => ['/content-tags', ['sort_order' => 1]],
]);

dataset('locale_title_store_cases', [
    'page' => ['/pages', Page::class, [
        'it' => ['title' => 'Pagina locale', 'abstract' => 'Sommario'],
        'content' => ['body' => 'Body'],
    ], 'title', 'Pagina locale'],
    'menu' => ['/menus', Menu::class, [
        'it' => ['title' => 'Menu locale'],
    ], 'title', 'Menu locale'],
    'menu-item' => ['/menu-items', MenuItem::class, [
        'menu_id' => null,
        'it' => ['title' => 'Voce locale', 'link' => '/it/voce-locale'],
    ], 'title', 'Voce locale'],
    'faq-category' => ['/faq-categories', FaqCategory::class, [
        'it' => ['title' => 'Categoria locale', 'abstract' => 'Sommario'],
    ], 'title', 'Categoria locale'],
    'faq' => ['/faqs', Faq::class, [
        'it' => ['title' => 'Domanda locale', 'content' => 'Risposta locale'],
    ], 'title', 'Domanda locale'],
    'mail-form' => ['/mail-forms', MailForm::class, [
        'it' => ['name' => 'Contatti', 'redirect_url' => 'https://example.com/it'],
    ], 'name', 'Contatti'],
    'modal' => ['/modals', Modal::class, [
        'it' => ['title' => 'Modale locale', 'content' => 'Contenuto locale', 'cta_button_text' => 'Apri'],
    ], 'title', 'Modale locale'],
    'content-tag' => ['/content-tags', ContentTag::class, [
        'sort_order' => 1,
        'it' => ['name' => 'Etichetta locale', 'abstract' => 'Sommario'],
    ], 'name', 'Etichetta locale'],
]);

dataset('locale_payload_key_validation_cases', [
    'page' => ['/pages', [
        'title' => 'Home',
        'content' => ['body' => 'Body'],
        'it' => ['title' => 'Casa', 'unknown' => 'nope'],
    ]],
    'menu' => ['/menus', [
        'title' => 'Main',
        'it' => ['title' => 'Principale', 'unknown' => 'nope'],
    ]],
    'menu-item' => ['/menu-items', [
        'menu_id' => null,
        'title' => 'Support',
        'it' => ['title' => 'Supporto', 'unknown' => 'nope'],
    ]],
    'faq-category' => ['/faq-categories', [
        'title' => 'General',
        'it' => ['title' => 'Generale', 'unknown' => 'nope'],
    ]],
    'faq' => ['/faqs', [
        'title' => 'Question',
        'content' => 'Answer',
        'it' => ['title' => 'Domanda', 'unknown' => 'nope'],
    ]],
    'mail-form' => ['/mail-forms', [
        'name' => 'Contact',
        'it' => ['name' => 'Contatti', 'unknown' => 'nope'],
    ]],
    'modal' => ['/modals', [
        'title' => 'Welcome',
        'content' => 'Hello',
        'it' => ['title' => 'Benvenuto', 'unknown' => 'nope'],
    ]],
    'content-tag' => ['/content-tags', [
        'name' => 'Summer',
        'sort_order' => 1,
        'it' => ['name' => 'Estate', 'unknown' => 'nope'],
    ]],
]);

dataset('translatable_update_without_title_cases', [
    'page' => [Page::class, '/pages', ['active' => true], 'active', true],
    'menu' => [Menu::class, '/menus', ['active' => true], 'active', true],
    'menu-item' => [MenuItem::class, '/menu-items', ['active' => true], 'active', true],
    'faq-category' => [FaqCategory::class, '/faq-categories', ['active' => true], 'active', true],
    'faq' => [Faq::class, '/faqs', ['active' => true], 'active', true],
    'mail-form' => [MailForm::class, '/mail-forms', ['email_to' => 'updated@example.com'], 'email_to', 'updated@example.com'],
    'modal' => [Modal::class, '/modals', ['timeout' => 30], 'timeout', 30],
    'content-tag' => [ContentTag::class, '/content-tags', ['sort_order' => 10], 'sort_order', 10],
]);

it('marks every content model as translatable with explicit translated attributes', function (string $modelClass, array $translatedAttributes) {
    $model = new $modelClass;

    expect($model)->toBeInstanceOf(TranslatableContract::class);
    expect($model->translatedAttributes)->toBe($translatedAttributes);
})->with('translatable_models');

it('requires title or localized title for translatable resource creation', function (string $uri, array $payload) {
    if ($uri === '/menu-items') {
        $payload['menu_id'] = Menu::factory()->create()->getKey();
    }

    postJson(config('contento.routes.api.v1.prefix') . $uri, $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors([str_contains($uri, 'content-tags') || str_contains($uri, 'mail-forms') ? 'name' : 'title']);
})->with('translatable_store_routes');

it('supports creation with localized title payloads for all translatable resources', function (string $uri, string $modelClass, array $payload, string $attribute, string $localizedTitle) {
    if ($uri === '/menu-items') {
        $payload['menu_id'] = Menu::factory()->create()->getKey();
    }

    postJson(config('contento.routes.api.v1.prefix') . $uri, $payload)->assertCreated();

    $model = $modelClass::query()->firstOrFail();

    assertDatabaseHas('translations', [
        'translatable_type' => $model->getMorphClass(),
        'translatable_id' => $model->getKey(),
        'locale' => 'it',
        'attribute' => $attribute,
        'value' => $localizedTitle,
    ]);
})->with('locale_title_store_cases');

it('rejects unknown localized payload keys for translatable resources', function (string $uri, array $payload) {
    if ($uri === '/menu-items') {
        $payload['menu_id'] = Menu::factory()->create()->getKey();
    }

    postJson(config('contento.routes.api.v1.prefix') . $uri, $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['it']);
})->with('locale_payload_key_validation_cases');

it('allows updating non-title fields without requiring titles again', function (string $modelClass, string $uri, array $payload, string $responseField, mixed $responseValue) {
    $model = $modelClass::factory()->create();

    putJson(config('contento.routes.api.v1.prefix') . $uri . '/' . $model->getKey(), $payload)
        ->assertOk()
        ->assertJsonPath('data.' . $responseField, $responseValue);
})->with('translatable_update_without_title_cases');
