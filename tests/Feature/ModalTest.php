<?php

use PictaStudio\Contento\Models\Modal;
use PictaStudio\Translatable\Locales;

use function Pest\Laravel\{assertDatabaseHas, getJson, postJson, putJson};

it('can list modals', function () {
    Modal::factory()->count(2)->create();

    getJson(config('contento.routes.api.v1.prefix') . '/modals')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('can filter, sort and paginate modals', function () {
    $first = Modal::factory()->create(['active' => true, 'timeout' => 10]);
    Modal::factory()->create(['active' => false, 'timeout' => 5]);
    $third = Modal::factory()->create(['active' => true, 'timeout' => 30]);

    $query = http_build_query([
        'id' => [$first->getKey(), $third->getKey()],
        'is_active' => 1,
        'timeout_min' => 10,
        'sort_by' => 'id',
        'sort_dir' => 'desc',
        'per_page' => 1,
        'page' => 1,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/modals?' . $query)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $third->getKey())
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 2);
});

it('rejects unsupported modal list query params', function () {
    getJson(config('contento.routes.api.v1.prefix') . '/modals?unknown=1')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unknown']);
});

it('can create a modal', function () {
    postJson(config('contento.routes.api.v1.prefix') . '/modals', [
        'title' => 'Welcome',
        'slug' => 'welcome',
        'content' => 'Hello!',
    ])
        ->assertCreated();

    $modal = Modal::query()->firstOrFail();

    assertDatabaseHas(config('contento.table_names.modals'), [
        'id' => $modal->getKey(),
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $modal->getMorphClass(),
        'translatable_id' => $modal->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'Welcome',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $modal->getMorphClass(),
        'translatable_id' => $modal->getKey(),
        'locale' => 'en',
        'attribute' => 'content',
        'value' => 'Hello!',
    ]);
});

it('can create a modal with multiple locale payload', function () {
    config()->set('translatable.locales', ['en', 'it', 'de']);
    app(Locales::class)->load();

    postJson(config('contento.routes.api.v1.prefix') . '/modals', [
        'en' => ['title' => 'Welcome', 'content' => 'Hello!', 'cta_button_text' => 'Continue', 'cta_button_url' => 'https://example.com/en'],
        'it' => ['title' => 'Benvenuto', 'content' => 'Ciao!', 'cta_button_text' => 'Continua', 'cta_button_url' => 'https://example.com/it'],
        'de' => ['title' => 'Willkommen', 'content' => 'Hallo!', 'cta_button_text' => 'Weiter', 'cta_button_url' => 'https://example.com/de'],
    ])
        ->assertCreated()
        ->assertJsonPath(contentoResourcePath('title'), 'Welcome');

    $modal = Modal::query()->firstOrFail();

    assertDatabaseHas(config('contento.table_names.modals'), [
        'id' => $modal->getKey(),
        'slug' => 'welcome',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $modal->getMorphClass(),
        'translatable_id' => $modal->getKey(),
        'locale' => 'de',
        'attribute' => 'cta_button_text',
        'value' => 'Weiter',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $modal->getMorphClass(),
        'translatable_id' => $modal->getKey(),
        'locale' => 'it',
        'attribute' => 'cta_button_url',
        'value' => 'https://example.com/it',
    ]);
});

it('can update a modal', function () {
    $modal = Modal::factory()->create([
        'title' => 'Old Title',
        'content' => 'Old content',
        'cta_button_text' => 'Old CTA',
    ]);

    putJson(config('contento.routes.api.v1.prefix') . '/modals/' . $modal->getKey(), [
        'title' => 'Updated Title',
        'content' => 'Updated content',
        'cta_button_text' => 'Updated CTA',
    ])
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('title'), 'Updated Title');

    assertDatabaseHas(config('contento.table_names.modals'), [
        'id' => $modal->getKey(),
        'slug' => 'updated-title',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $modal->getMorphClass(),
        'translatable_id' => $modal->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'Updated Title',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $modal->getMorphClass(),
        'translatable_id' => $modal->getKey(),
        'locale' => 'en',
        'attribute' => 'content',
        'value' => 'Updated content',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $modal->getMorphClass(),
        'translatable_id' => $modal->getKey(),
        'locale' => 'en',
        'attribute' => 'cta_button_text',
        'value' => 'Updated CTA',
    ]);
});
