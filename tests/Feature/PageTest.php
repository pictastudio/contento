<?php

use PictaStudio\Contento\Models\Page;

use function Pest\Laravel\{assertDatabaseHas, assertDatabaseMissing, deleteJson, getJson, postJson, putJson};

it('can list pages', function () {
    Page::factory()->count(3)->create();

    getJson(config('contento.prefix') . '/pages')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('can show a page by id', function () {
    $page = Page::factory()->create();

    getJson(config('contento.prefix') . '/pages/' . $page->getKey())
        ->assertOk()
        ->assertJsonPath('data.title', $page->title);
});

it('can show a page by slug', function () {
    $page = Page::factory()->create(['slug' => 'test-slug']);

    getJson(config('contento.prefix') . '/pages/test-slug')
        ->assertOk()
        ->assertJsonPath('data.id', $page->getKey());
});

it('can create a page', function () {
    $data = [
        'title' => 'New Page',
        'content' => ['body' => 'Hello World'],
    ];

    postJson(config('contento.prefix') . '/pages', $data)
        ->assertCreated()
        ->assertJsonPath('data.title', 'New Page');

    $page = Page::query()->firstOrFail();

    assertDatabaseHas(config('contento.table_names.pages'), [
        'id' => $page->getKey(),
        'slug' => 'new-page',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'New Page',
    ]);
});

it('can update a page', function () {
    $page = Page::factory()->create();

    putJson(config('contento.prefix') . '/pages/' . $page->getKey(), [
        'title' => 'Updated Title',
    ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated Title');

    assertDatabaseHas(config('contento.table_names.pages'), [
        'id' => $page->getKey(),
        'slug' => 'updated-title',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'Updated Title',
    ]);
});

it('can delete a page', function () {
    $page = Page::factory()->create();

    deleteJson(config('contento.prefix') . '/pages/' . $page->getKey())
        ->assertNoContent();

    assertDatabaseMissing(config('contento.table_names.pages'), [
        'id' => $page->getKey(),
    ]);
});

it('slug creates correctly', function () {
    $data = [
        'title' => 'New Page',
        'content' => ['body' => 'Hello World'],
    ];

    postJson(config('contento.prefix') . '/pages', $data)
        ->assertCreated()
        ->assertJsonPath('data.title', 'New Page');

    $data = [
        'title' => 'New Page',
        'content' => ['body' => 'Hello World'],
    ];

    postJson(config('contento.prefix') . '/pages', $data)
        ->assertCreated()
        ->assertJsonPath('data.title', 'New Page');

    $page = Page::query()
        ->where('slug', 'new-page-1')
        ->firstOrFail();

    assertDatabaseHas(config('contento.table_names.pages'), [
        'id' => $page->getKey(),
        'slug' => 'new-page-1',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'New Page',
    ]);
});
