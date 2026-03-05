<?php

use PictaStudio\Contento\Models\{ContentTag, Faq, FaqCategory, Modal, Page};
use PictaStudio\Translatable\Contracts\Translatable as TranslatableContract;

use function Pest\Laravel\{assertDatabaseHas, postJson, putJson};

dataset('translatable_models', [
    'page' => [Page::class, ['title', 'abstract', 'slug']],
    'faq-category' => [FaqCategory::class, ['title', 'abstract', 'slug']],
    'faq' => [Faq::class, ['title', 'content', 'slug']],
    'modal' => [Modal::class, ['title', 'content', 'cta_button_text', 'slug']],
    'content-tag' => [ContentTag::class, ['name', 'slug', 'abstract', 'description']],
]);

dataset('translatable_store_routes', [
    'page' => ['/pages', ['content' => ['body' => 'Body']]],
    'faq-category' => ['/faq-categories', ['active' => true]],
    'faq' => ['/faqs', ['content' => 'Answer']],
    'modal' => ['/modals', ['content' => 'Body', 'timeout' => 10]],
    'content-tag' => ['/content-tags', ['sort_order' => 1]],
]);

dataset('locale_title_store_cases', [
    'page' => ['/pages', Page::class, [
        'it' => ['title' => 'Pagina locale', 'abstract' => 'Sommario'],
        'content' => ['body' => 'Body'],
    ], 'title', 'Pagina locale'],
    'faq-category' => ['/faq-categories', FaqCategory::class, [
        'it' => ['title' => 'Categoria locale', 'abstract' => 'Sommario'],
    ], 'title', 'Categoria locale'],
    'faq' => ['/faqs', Faq::class, [
        'it' => ['title' => 'Domanda locale', 'content' => 'Risposta locale'],
    ], 'title', 'Domanda locale'],
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
    'faq-category' => ['/faq-categories', [
        'title' => 'General',
        'it' => ['title' => 'Generale', 'unknown' => 'nope'],
    ]],
    'faq' => ['/faqs', [
        'title' => 'Question',
        'content' => 'Answer',
        'it' => ['title' => 'Domanda', 'unknown' => 'nope'],
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
    'faq-category' => [FaqCategory::class, '/faq-categories', ['active' => true], 'active', true],
    'faq' => [Faq::class, '/faqs', ['active' => true], 'active', true],
    'modal' => [Modal::class, '/modals', ['timeout' => 30], 'timeout', 30],
    'content-tag' => [ContentTag::class, '/content-tags', ['sort_order' => 10], 'sort_order', 10],
]);

it('marks every content model as translatable with explicit translated attributes', function (string $modelClass, array $translatedAttributes) {
    $model = new $modelClass;

    expect($model)->toBeInstanceOf(TranslatableContract::class);
    expect($model->translatedAttributes)->toBe($translatedAttributes);
})->with('translatable_models');

it('requires title or localized title for translatable resource creation', function (string $uri, array $payload) {
    postJson(config('contento.prefix') . $uri, $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors([str_contains($uri, 'content-tags') ? 'name' : 'title']);
})->with('translatable_store_routes');

it('supports creation with localized title payloads for all translatable resources', function (string $uri, string $modelClass, array $payload, string $attribute, string $localizedTitle) {
    postJson(config('contento.prefix') . $uri, $payload)->assertCreated();

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
    postJson(config('contento.prefix') . $uri, $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['it']);
})->with('locale_payload_key_validation_cases');

it('allows updating non-title fields without requiring titles again', function (string $modelClass, string $uri, array $payload, string $responseField, mixed $responseValue) {
    $model = $modelClass::factory()->create();

    putJson(config('contento.prefix') . $uri . '/' . $model->getKey(), $payload)
        ->assertOk()
        ->assertJsonPath('data.' . $responseField, $responseValue);
})->with('translatable_update_without_title_cases');
