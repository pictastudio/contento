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

    getJson(config('contento.prefix') . '/pages/' . $page->id)
        ->assertOk()
        ->assertJsonPath('data.title', $page->title);
});

it('can show a page by slug', function () {
    $page = Page::factory()->create(['slug' => 'test-slug']);

    getJson(config('contento.prefix') . '/pages/test-slug')
        ->assertOk()
        ->assertJsonPath('data.id', $page->id);
});

it('can create a page', function () {
    $data = [
        'title' => 'New Page',
        'slug' => 'new-page',
        'content' => ['body' => 'Hello World'],
    ];

    postJson(config('contento.prefix') . '/pages', $data)
        ->assertCreated()
        ->assertJsonPath('data.title', 'New Page');

    assertDatabaseHas(config('contento.table_names.pages'), [
        'title' => 'New Page',
        'slug' => 'new-page',
    ]);
});

it('can update a page', function () {
    $page = Page::factory()->create();

    putJson(config('contento.prefix') . '/pages/' . $page->id, [
        'title' => 'Updated Title',
        'slug' => 'updated-slug',
    ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated Title');

    assertDatabaseHas(config('contento.table_names.pages'), [
        'id' => $page->id,
        'title' => 'Updated Title',
    ]);
});

it('can delete a page', function () {
    $page = Page::factory()->create();

    deleteJson(config('contento.prefix') . '/pages/' . $page->id)
        ->assertNoContent();

    assertDatabaseMissing(config('contento.table_names.pages'), [
        'id' => $page->id,
    ]);
});
