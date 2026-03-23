<?php

use PictaStudio\Contento\Models\Setting;

use function Pest\Laravel\{assertDatabaseHas, assertDatabaseMissing, deleteJson, getJson, postJson};

it('can list settings', function () {
    Setting::factory()->count(2)->create(['group' => 'testing']);
    Setting::factory()->create(['group' => 'other']);

    getJson(config('contento.routes.api.v1.prefix') . '/settings?group=' . urlencode('EST'))
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('can filter, sort and paginate settings', function () {
    $first = Setting::factory()->create(['group' => 'site']);
    Setting::factory()->create(['group' => 'other']);
    $third = Setting::factory()->create(['group' => 'site']);

    $query = http_build_query([
        'id' => [$first->getKey(), $third->getKey()],
        'group' => 'site',
        'sort_by' => 'id',
        'sort_dir' => 'desc',
        'per_page' => 1,
        'page' => 1,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/settings?' . $query)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $third->getKey())
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 2);
});

it('rejects unsupported setting list query params', function () {
    getJson(config('contento.routes.api.v1.prefix') . '/settings?unknown=1')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unknown']);
});

it('can create or update a setting', function () {
    postJson(config('contento.routes.api.v1.prefix') . '/settings', [
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

it('can bulk update settings using id or group and name', function () {
    $byId = Setting::factory()->create([
        'group' => 'site',
        'name' => 'title',
        'value' => 'Old Site Title',
    ]);
    $byGroupAndName = Setting::factory()->create([
        'group' => 'seo',
        'name' => 'description',
        'value' => 'Old Description',
    ]);

    postJson(config('contento.routes.api.v1.prefix') . '/settings/bulk/update', [
        'settings' => [
            [
                'id' => $byId->getKey(),
                'value' => 'New Site Title',
            ],
            [
                'group' => $byGroupAndName->group,
                'name' => $byGroupAndName->name,
                'value' => 'New Description',
            ],
            [
                'group' => 'site',
                'name' => 'tagline',
                'value' => 'A new tagline',
            ],
        ],
    ])
        ->assertOk()
        ->assertJsonCount(3, 'data');

    assertDatabaseHas(config('contento.table_names.settings'), [
        'id' => $byId->getKey(),
        'group' => 'site',
        'name' => 'title',
        'value' => 'New Site Title',
    ]);
    assertDatabaseHas(config('contento.table_names.settings'), [
        'id' => $byGroupAndName->getKey(),
        'group' => 'seo',
        'name' => 'description',
        'value' => 'New Description',
    ]);
    assertDatabaseHas(config('contento.table_names.settings'), [
        'group' => 'site',
        'name' => 'tagline',
        'value' => 'A new tagline',
    ]);
});

it('validates setting identity in bulk updates', function () {
    postJson(config('contento.routes.api.v1.prefix') . '/settings/bulk/update', [
        'settings' => [
            [
                'value' => 'Missing keys',
            ],
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'settings.0.group',
            'settings.0.name',
        ]);
});

it('can delete a setting', function () {
    $setting = Setting::factory()->create();

    deleteJson(config('contento.routes.api.v1.prefix') . '/settings/' . $setting->getKey())
        ->assertNoContent();

    assertDatabaseMissing(config('contento.table_names.settings'), [
        'id' => $setting->getKey(),
    ]);
});
