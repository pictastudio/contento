<?php

use PictaStudio\Contento\Models\Modal;

use function Pest\Laravel\{assertDatabaseHas, getJson, postJson};

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

    assertDatabaseHas(config('contento.table_names.modals'), [
        'title' => 'Welcome',
    ]);
});
