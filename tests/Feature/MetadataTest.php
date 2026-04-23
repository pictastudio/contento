<?php

use PictaStudio\Contento\Models\Metadata;

use function Pest\Laravel\{assertDatabaseHas, assertDatabaseMissing, deleteJson, getJson, patchJson, postJson};

it('can list metadata', function () {
    Metadata::factory()->create(['name' => 'Products metadata']);
    Metadata::factory()->create(['name' => 'Products seo']);
    Metadata::factory()->create(['name' => 'Contacts metadata']);

    getJson(config('contento.routes.api.v1.prefix') . '/metadata?name=' . urlencode('PROD'))
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('can filter, sort, and paginate metadata', function () {
    $first = Metadata::factory()->create([
        'name' => 'Products metadata',
        'slug' => 'products-metadata',
    ]);
    Metadata::factory()->create([
        'name' => 'Contacts metadata',
        'slug' => 'contacts-metadata',
    ]);
    $third = Metadata::factory()->create([
        'name' => 'Products seo metadata',
        'slug' => 'products-seo-metadata',
    ]);

    $query = http_build_query([
        'id' => [$first->getKey(), $third->getKey()],
        'name' => 'products',
        'slug' => 'metadata',
        'sort_by' => 'id',
        'sort_dir' => 'desc',
        'per_page' => 1,
        'page' => 1,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/metadata?' . $query)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $third->getKey())
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 2);
});

it('can filter metadata by exact uri', function () {
    $metadata = Metadata::factory()->create(['uri' => '/prodotti']);
    Metadata::factory()->create(['uri' => '/contatti']);

    getJson(config('contento.routes.api.v1.prefix') . '/metadata?uri=' . urlencode($metadata->uri))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $metadata->getKey());
});

it('rejects unsupported metadata list query params', function () {
    getJson(config('contento.routes.api.v1.prefix') . '/metadata?unknown=1')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unknown']);
});

it('can create metadata with an explicit slug', function () {
    postJson(config('contento.routes.api.v1.prefix') . '/metadata', [
        'name' => 'Products metadata',
        'slug' => 'products-meta',
        'uri' => '/prodotti',
        'metadata' => [
            'title' => 'Products',
            'description' => 'SEO description',
        ],
    ])
        ->assertCreated()
        ->assertJsonPath(contentoResourcePath('slug'), 'products-meta')
        ->assertJsonPath(contentoResourcePath('metadata.title'), 'Products');

    assertDatabaseHas(config('contento.table_names.metadata'), [
        'name' => 'Products metadata',
        'slug' => 'products-meta',
        'uri' => '/prodotti',
    ]);
});

it('can create metadata with a generated slug', function () {
    postJson(config('contento.routes.api.v1.prefix') . '/metadata', [
        'name' => 'Products metadata',
        'uri' => '/prodotti',
        'metadata' => [
            'title' => 'Products',
        ],
    ])
        ->assertCreated()
        ->assertJsonPath(contentoResourcePath('slug'), 'products-metadata');

    assertDatabaseHas(config('contento.table_names.metadata'), [
        'name' => 'Products metadata',
        'slug' => 'products-metadata',
        'uri' => '/prodotti',
    ]);
});

it('can show metadata by id and slug', function () {
    $metadata = Metadata::factory()->create([
        'slug' => 'products-meta',
        'uri' => '/prodotti',
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/metadata/' . $metadata->getKey())
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $metadata->getKey());

    getJson(config('contento.routes.api.v1.prefix') . '/metadata/' . $metadata->slug)
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $metadata->getKey());
});

it('can update metadata by slug and keep the same slug and uri values', function () {
    $metadata = Metadata::factory()->create([
        'name' => 'Products metadata',
        'slug' => 'products-meta',
        'uri' => '/prodotti',
        'metadata' => [
            'title' => 'Products',
        ],
    ]);

    patchJson(config('contento.routes.api.v1.prefix') . '/metadata/' . $metadata->slug, [
        'name' => 'Products metadata updated',
        'slug' => 'products-meta',
        'uri' => '/prodotti',
        'metadata' => [
            'title' => 'Updated products',
        ],
    ])
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('name'), 'Products metadata updated')
        ->assertJsonPath(contentoResourcePath('slug'), 'products-meta')
        ->assertJsonPath(contentoResourcePath('uri'), '/prodotti')
        ->assertJsonPath(contentoResourcePath('metadata.title'), 'Updated products');

    assertDatabaseHas(config('contento.table_names.metadata'), [
        'id' => $metadata->getKey(),
        'name' => 'Products metadata updated',
        'slug' => 'products-meta',
        'uri' => '/prodotti',
    ]);
});

it('can delete metadata by slug', function () {
    $metadata = Metadata::factory()->create([
        'slug' => 'products-meta',
    ]);

    deleteJson(config('contento.routes.api.v1.prefix') . '/metadata/' . $metadata->slug)
        ->assertNoContent();

    assertDatabaseMissing(config('contento.table_names.metadata'), [
        'id' => $metadata->getKey(),
    ]);
});

it('validates unique metadata slugs', function () {
    Metadata::factory()->create(['slug' => 'products-meta']);

    postJson(config('contento.routes.api.v1.prefix') . '/metadata', [
        'name' => 'Products metadata',
        'slug' => 'products-meta',
        'uri' => '/prodotti',
        'metadata' => [
            'title' => 'Products',
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});

it('validates unique metadata uris', function () {
    Metadata::factory()->create(['uri' => '/prodotti']);

    postJson(config('contento.routes.api.v1.prefix') . '/metadata', [
        'name' => 'Products metadata',
        'slug' => 'products-meta',
        'uri' => '/prodotti',
        'metadata' => [
            'title' => 'Products',
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['uri']);
});

it('persists and returns metadata json payloads', function () {
    $response = postJson(config('contento.routes.api.v1.prefix') . '/metadata', [
        'name' => 'Products metadata',
        'uri' => '/prodotti',
        'metadata' => [
            'title' => 'Products',
            'open_graph' => [
                'title' => 'Products OG',
            ],
        ],
    ])
        ->assertCreated()
        ->assertJsonPath(contentoResourcePath('metadata.open_graph.title'), 'Products OG');

    $metadata = Metadata::query()->findOrFail($response->json(contentoResourcePath('id')));

    expect($metadata->metadata)->toBe([
        'title' => 'Products',
        'open_graph' => [
            'title' => 'Products OG',
        ],
    ]);
});
