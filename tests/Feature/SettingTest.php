<?php

use PictaStudio\Contento\Models\Setting;

use function Pest\Laravel\{assertDatabaseHas, assertDatabaseMissing, deleteJson, getJson, postJson};

it('can list settings', function () {
    Setting::factory()->count(2)->create();

    getJson(config('contento.prefix') . '/settings')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('can create or update a setting', function () {
    postJson(config('contento.prefix') . '/settings', [
        'group' => 'site',
        'name' => 'title',
        'value' => 'My Site',
    ])
        ->assertCreated();

    assertDatabaseHas(config('contento.table_names.settings'), [
        'group' => 'site',
        'name' => 'title',
        'value' => 'My Site',
    ]);
});

it('can delete a setting', function () {
    $setting = Setting::factory()->create();

    deleteJson(config('contento.prefix') . '/settings/' . $setting->getKey())
        ->assertNoContent();

    assertDatabaseMissing(config('contento.table_names.settings'), [
        'id' => $setting->getKey(),
    ]);
});
