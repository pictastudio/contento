<?php

use PictaStudio\Contento\Models\Modal;
use PictaStudio\Translatable\Locales;

use function Pest\Laravel\{assertDatabaseHas, getJson, postJson, putJson};

it('can list modals', function () {
    Modal::factory()->count(2)->create();

    getJson(config('contento.prefix') . '/modals')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('can create a modal', function () {
    postJson(config('contento.prefix') . '/modals', [
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

    postJson(config('contento.prefix') . '/modals', [
        'en' => ['title' => 'Welcome', 'content' => 'Hello!', 'cta_button_text' => 'Continue'],
        'it' => ['title' => 'Benvenuto', 'content' => 'Ciao!', 'cta_button_text' => 'Continua'],
        'de' => ['title' => 'Willkommen', 'content' => 'Hallo!', 'cta_button_text' => 'Weiter'],
    ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Welcome');

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
});

it('can update a modal', function () {
    $modal = Modal::factory()->create([
        'title' => 'Old Title',
        'content' => 'Old content',
        'cta_button_text' => 'Old CTA',
    ]);

    putJson(config('contento.prefix') . '/modals/' . $modal->getKey(), [
        'title' => 'Updated Title',
        'content' => 'Updated content',
        'cta_button_text' => 'Updated CTA',
    ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated Title');

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
