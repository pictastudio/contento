<?php

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Gate, Storage};
use PictaStudio\Contento\Models\{Gallery, GalleryItem};

use function Pest\Laravel\{actingAs, assertDatabaseHas, assertDatabaseMissing, deleteJson, getJson, patchJson, post, postJson};

it('can create update show and delete galleries', function () {
    postJson(config('contento.routes.api.v1.prefix') . '/galleries', [
        'title' => 'Homepage Gallery',
        'code' => 'homepage-gallery',
        'abstract' => 'Homepage visuals',
    ])->assertCreated()
        ->assertJsonPath(contentoResourcePath('title'), 'Homepage Gallery')
        ->assertJsonPath(contentoResourcePath('slug'), 'homepage-gallery')
        ->assertJsonPath(contentoResourcePath('code'), 'homepage-gallery');

    $gallery = Gallery::query()->firstOrFail();

    getJson(config('contento.routes.api.v1.prefix') . '/galleries/' . $gallery->code)
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $gallery->getKey());

    patchJson(config('contento.routes.api.v1.prefix') . '/galleries/' . $gallery->slug, [
        'title' => 'Updated Gallery',
        'code' => 'updated-gallery',
        'active' => false,
    ])->assertOk()
        ->assertJsonPath(contentoResourcePath('title'), 'Updated Gallery')
        ->assertJsonPath(contentoResourcePath('code'), 'updated-gallery')
        ->assertJsonPath(contentoResourcePath('active'), false);

    deleteJson(config('contento.routes.api.v1.prefix') . '/galleries/' . $gallery->getKey())
        ->assertNoContent();

    assertDatabaseMissing(config('contento.table_names.galleries'), [
        'id' => $gallery->getKey(),
    ]);
});

it('can filter sort paginate and include gallery items', function () {
    $first = Gallery::factory()->create([
        'title' => 'Summer Gallery',
        'code' => 'summer',
        'active' => true,
    ]);
    Gallery::factory()->create([
        'title' => 'Winter Gallery',
        'code' => 'winter',
        'active' => false,
    ]);
    $third = Gallery::factory()->create([
        'title' => 'Summer Events',
        'code' => 'summer-events',
        'active' => true,
    ]);
    GalleryItem::factory()->for($third, 'gallery')->create([
        'title' => 'Included item',
        'sort_order' => 1,
    ]);

    $query = http_build_query([
        'id' => [$first->getKey(), $third->getKey()],
        'title' => 'summer',
        'is_active' => 1,
        'sort_by' => 'code',
        'sort_dir' => 'desc',
        'per_page' => 1,
        'include' => 'gallery_items',
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/galleries?' . $query)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $third->getKey())
        ->assertJsonPath('data.0.items.0.title', 'Included item')
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 2);

    getJson(config('contento.routes.api.v1.prefix') . '/galleries/' . $third->getKey() . '?include=gallery_items')
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('items.0.title'), 'Included item');
});

it('can list all galleries with the all filter', function () {
    Gallery::factory()->create(['active' => true]);
    Gallery::factory()->create(['active' => false]);

    getJson(config('contento.routes.api.v1.prefix') . '/galleries?all=1&sort_by=id&sort_dir=asc')
        ->assertOk()
        ->assertJsonCount(2, contentoCollectionPath())
        ->assertJsonMissingPath('meta')
        ->assertJsonMissingPath('links');
});

it('can update a gallery while creating and updating nested gallery items', function () {
    $gallery = Gallery::factory()->create([
        'title' => 'Original gallery',
    ]);
    $existing = GalleryItem::factory()->for($gallery, 'gallery')->create([
        'title' => 'Original item',
        'sort_order' => 10,
    ]);
    $untouched = GalleryItem::factory()->for($gallery, 'gallery')->create([
        'title' => 'Untouched item',
        'sort_order' => 30,
    ]);

    patchJson(config('contento.routes.api.v1.prefix') . '/galleries/' . $gallery->getKey(), [
        'title' => 'Updated gallery',
        'gallery_items' => [
            [
                'id' => $existing->getKey(),
                'title' => 'Updated item',
                'sort_order' => 1,
                'links' => [
                    ['label' => 'Updated link', 'url' => 'https://example.com/updated'],
                ],
            ],
            [
                'title' => 'Created item',
                'subtitle' => 'Created subtitle',
                'description' => 'Created description',
                'sort_order' => 2,
                'active' => true,
                'links' => [],
            ],
        ],
    ])->assertOk()
        ->assertJsonPath(contentoResourcePath('title'), 'Updated gallery')
        ->assertJsonPath(contentoResourcePath('items.0.title'), 'Updated item')
        ->assertJsonPath(contentoResourcePath('items.1.title'), 'Created item')
        ->assertJsonPath(contentoResourcePath('items.2.title'), 'Untouched item');

    assertDatabaseHas(config('contento.table_names.gallery_items'), [
        'id' => $existing->getKey(),
        'gallery_id' => $gallery->getKey(),
        'title' => 'Updated item',
        'sort_order' => 1,
    ]);

    assertDatabaseHas(config('contento.table_names.gallery_items'), [
        'gallery_id' => $gallery->getKey(),
        'title' => 'Created item',
        'subtitle' => 'Created subtitle',
        'sort_order' => 2,
    ]);

    assertDatabaseHas(config('contento.table_names.gallery_items'), [
        'id' => $untouched->getKey(),
        'title' => 'Untouched item',
    ]);
});

it('can create and update nested gallery item images through gallery updates', function () {
    Storage::fake('public');

    $gallery = Gallery::factory()->create();

    $response = post(
        config('contento.routes.api.v1.prefix') . '/galleries/' . $gallery->getKey(),
        [
            '_method' => 'PATCH',
            'gallery_items' => [
                [
                    'title' => 'Created with image',
                    'sort_order' => 1,
                    'img' => [
                        'file' => UploadedFile::fake()->image('nested.jpg', 640, 480),
                        'alt' => 'Nested image alt',
                        'name' => 'Nested image',
                        'metadata' => ['focal' => 'center'],
                    ],
                ],
            ],
        ],
        ['Accept' => 'application/json']
    )->assertOk()
        ->assertJsonPath(contentoResourcePath('items.0.title'), 'Created with image')
        ->assertJsonPath(contentoResourcePath('items.0.img.alt'), 'Nested image alt')
        ->assertJsonPath(contentoResourcePath('items.0.img.metadata.focal'), 'center');

    $galleryItem = GalleryItem::query()->findOrFail($response->json(contentoResourcePath('items.0.id')));
    $imageId = (string) data_get($galleryItem->img, 'id');

    Storage::disk('public')->assertExists((string) data_get($galleryItem->img, 'src'));

    patchJson(config('contento.routes.api.v1.prefix') . '/galleries/' . $gallery->getKey(), [
        'gallery_items' => [
            [
                'id' => $galleryItem->getKey(),
                'img' => [
                    'id' => $imageId,
                    'alt' => 'Updated nested alt',
                    'metadata' => ['focal' => 'top'],
                ],
            ],
        ],
    ])->assertOk()
        ->assertJsonPath(contentoResourcePath('items.0.img.id'), $imageId)
        ->assertJsonPath(contentoResourcePath('items.0.img.alt'), 'Updated nested alt')
        ->assertJsonPath(contentoResourcePath('items.0.img.metadata.focal'), 'top');
});

it('validates nested gallery item upserts on gallery update', function () {
    $gallery = Gallery::factory()->create();
    $otherGallery = Gallery::factory()->create();
    $galleryItem = GalleryItem::factory()->for($gallery, 'gallery')->create();
    $otherGalleryItem = GalleryItem::factory()->for($otherGallery, 'gallery')->create();

    patchJson(config('contento.routes.api.v1.prefix') . '/galleries/' . $gallery->getKey(), [
        'gallery_items' => [
            ['id' => $galleryItem->getKey()],
            ['id' => $galleryItem->getKey()],
        ],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['gallery_items.1.id']);

    patchJson(config('contento.routes.api.v1.prefix') . '/galleries/' . $gallery->getKey(), [
        'gallery_items' => [
            ['sort_order' => 1],
        ],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['gallery_items.0.title']);

    patchJson(config('contento.routes.api.v1.prefix') . '/galleries/' . $gallery->getKey(), [
        'gallery_items' => [
            ['id' => 999999],
        ],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['gallery_items']);

    patchJson(config('contento.routes.api.v1.prefix') . '/galleries/' . $gallery->getKey(), [
        'gallery_items' => [
            ['id' => $otherGalleryItem->getKey()],
        ],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['gallery_items']);

    patchJson(config('contento.routes.api.v1.prefix') . '/galleries/' . $gallery->getKey(), [
        'gallery_items' => [
            [
                'title' => 'Wrong owner',
                'gallery_id' => $otherGallery->getKey(),
            ],
        ],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['gallery_items.0.gallery_id']);
});

it('rejects invalid gallery payloads and unsupported query params', function () {
    Gallery::factory()->create(['code' => 'duplicate-code']);

    postJson(config('contento.routes.api.v1.prefix') . '/galleries', [
        'title' => 'Duplicate',
        'code' => 'duplicate-code',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['code']);

    postJson(config('contento.routes.api.v1.prefix') . '/galleries', [
        'code' => 'missing-title',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);

    getJson(config('contento.routes.api.v1.prefix') . '/galleries?unknown=1')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unknown']);
});

it('can create update show and delete gallery items with inline images', function () {
    Storage::fake('public');
    $gallery = Gallery::factory()->create();

    $response = post(
        config('contento.routes.api.v1.prefix') . '/gallery-items',
        [
            'gallery_id' => $gallery->getKey(),
            'title' => 'Hero slide',
            'subtitle' => 'Lead visual',
            'description' => 'Slide description',
            'sort_order' => 2,
            'links' => [
                ['label' => 'Read more', 'url' => 'https://example.com'],
            ],
            'img' => [
                'file' => UploadedFile::fake()->image('hero.jpg', 640, 480),
                'alt' => 'Hero alt',
                'name' => 'Hero image',
                'metadata' => ['focal' => 'center'],
            ],
        ],
        ['Accept' => 'application/json']
    )->assertCreated()
        ->assertJsonPath(contentoResourcePath('title'), 'Hero slide')
        ->assertJsonPath(contentoResourcePath('links.0.label'), 'Read more')
        ->assertJsonPath(contentoResourcePath('img.alt'), 'Hero alt')
        ->assertJsonPath(contentoResourcePath('img.metadata.focal'), 'center');

    $galleryItem = GalleryItem::query()->findOrFail($response->json(contentoResourcePath('id')));
    $imageId = (string) data_get($galleryItem->img, 'id');

    expect(str_starts_with((string) data_get($galleryItem->img, 'src'), 'gallery_items/' . $galleryItem->getKey() . '/img/'))->toBeTrue();
    Storage::disk('public')->assertExists((string) data_get($galleryItem->img, 'src'));

    patchJson(config('contento.routes.api.v1.prefix') . '/gallery-items/' . $galleryItem->getKey(), [
        'img' => [
            'id' => $imageId,
            'alt' => 'Updated alt',
            'metadata' => ['focal' => 'top'],
        ],
    ])->assertOk()
        ->assertJsonPath(contentoResourcePath('img.id'), $imageId)
        ->assertJsonPath(contentoResourcePath('img.alt'), 'Updated alt')
        ->assertJsonPath(contentoResourcePath('img.metadata.focal'), 'top');

    patchJson(config('contento.routes.api.v1.prefix') . '/gallery-items/' . $galleryItem->getKey(), [
        'img' => [
            'alt' => 'Updated alt without id',
        ],
    ])->assertOk()
        ->assertJsonPath(contentoResourcePath('img.id'), $imageId)
        ->assertJsonPath(contentoResourcePath('img.alt'), 'Updated alt without id');

    getJson(config('contento.routes.api.v1.prefix') . '/gallery-items/' . $galleryItem->getKey() . '?include=gallery')
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('gallery.id'), $gallery->getKey());

    deleteJson(config('contento.routes.api.v1.prefix') . '/gallery-items/' . $galleryItem->getKey())
        ->assertNoContent();

    assertDatabaseMissing(config('contento.table_names.gallery_items'), [
        'id' => $galleryItem->getKey(),
    ]);
});

it('filters scopes orders and lists gallery items', function () {
    $gallery = Gallery::factory()->create();
    $lowest = GalleryItem::factory()->for($gallery, 'gallery')->create([
        'title' => 'Lowest',
        'sort_order' => 1,
        'active' => true,
        'visible_from' => now()->subDay(),
        'visible_until' => now()->addDay(),
    ]);
    $highest = GalleryItem::factory()->for($gallery, 'gallery')->create([
        'title' => 'Highest',
        'sort_order' => 20,
        'active' => true,
        'visible_from' => now()->subDay(),
        'visible_until' => now()->addDay(),
    ]);
    $inactive = GalleryItem::factory()->for($gallery, 'gallery')->create([
        'title' => 'Inactive',
        'active' => false,
        'visible_from' => now()->subDay(),
        'visible_until' => now()->addDay(),
    ]);
    $future = GalleryItem::factory()->for($gallery, 'gallery')->create([
        'title' => 'Future',
        'active' => true,
        'visible_from' => now()->addDay(),
        'visible_until' => null,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/gallery-items?gallery_id=' . $gallery->getKey())
        ->assertOk()
        ->assertJsonPath('data.0.id', $lowest->getKey())
        ->assertJsonPath('data.1.id', $highest->getKey())
        ->assertJsonMissing(['id' => $inactive->getKey()])
        ->assertJsonMissing(['id' => $future->getKey()]);

    getJson(config('contento.routes.api.v1.prefix') . '/gallery-items?all=1&sort_by=id&sort_dir=asc')
        ->assertOk()
        ->assertJsonCount(4, contentoCollectionPath());

    getJson(config('contento.routes.api.v1.prefix') . '/gallery-items?exclude_active_scope=1&sort_by=id&sort_dir=asc')
        ->assertOk()
        ->assertJsonFragment(['id' => $inactive->getKey()])
        ->assertJsonMissing(['id' => $future->getKey()]);

    getJson(config('contento.routes.api.v1.prefix') . '/gallery-items?visible_from_start=' . urlencode($future->visible_from?->toDateTimeString() ?? ''))
        ->assertOk()
        ->assertJsonFragment(['id' => $future->getKey()]);
});

it('rejects invalid gallery item payloads and unsupported query params', function () {
    $gallery = Gallery::factory()->create();
    $galleryItem = GalleryItem::factory()->for($gallery, 'gallery')->create();

    postJson(config('contento.routes.api.v1.prefix') . '/gallery-items', [
        'gallery_id' => $gallery->getKey(),
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);

    patchJson(config('contento.routes.api.v1.prefix') . '/gallery-items/' . $galleryItem->getKey(), [
        'img' => [
            'id' => 'missing-image',
            'alt' => 'Invalid',
        ],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['img.id', 'img.file']);

    getJson(config('contento.routes.api.v1.prefix') . '/gallery-items?unknown=1')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unknown']);
});

it('uses gallery and gallery item policy hooks when configured', function () {
    config(['contento.authorize_using_policies' => true]);
    Gate::policy(Gallery::class, TestGalleryPolicyCreateDenied::class);
    Gate::policy(GalleryItem::class, TestGalleryItemPolicyUpdateDenied::class);

    actingAs(new GenericUser(['id' => 1]));

    postJson(config('contento.routes.api.v1.prefix') . '/galleries', [
        'title' => 'Denied gallery',
        'code' => 'denied-gallery',
    ])->assertForbidden();

    $galleryItem = GalleryItem::factory()->create();

    patchJson(config('contento.routes.api.v1.prefix') . '/gallery-items/' . $galleryItem->getKey(), [
        'title' => 'Denied update',
    ])->assertForbidden();
});

it('uses gallery item policy hooks for nested gallery updates', function () {
    config(['contento.authorize_using_policies' => true]);
    Gate::policy(GalleryItem::class, TestGalleryItemPolicyCreateAndUpdateDenied::class);

    actingAs(new GenericUser(['id' => 1]));

    $gallery = Gallery::factory()->create();
    $galleryItem = GalleryItem::factory()->for($gallery, 'gallery')->create();

    patchJson(config('contento.routes.api.v1.prefix') . '/galleries/' . $gallery->getKey(), [
        'gallery_items' => [
            [
                'id' => $galleryItem->getKey(),
                'title' => 'Denied nested update',
            ],
        ],
    ])->assertForbidden();

    patchJson(config('contento.routes.api.v1.prefix') . '/galleries/' . $gallery->getKey(), [
        'gallery_items' => [
            [
                'title' => 'Denied nested create',
            ],
        ],
    ])->assertForbidden();
});

class TestGalleryPolicyCreateDenied
{
    public function create(Authenticatable $user): bool
    {
        return false;
    }
}

class TestGalleryItemPolicyUpdateDenied
{
    public function update(Authenticatable $user, GalleryItem $galleryItem): bool
    {
        return false;
    }
}

class TestGalleryItemPolicyCreateAndUpdateDenied
{
    public function create(Authenticatable $user): bool
    {
        return false;
    }

    public function update(Authenticatable $user, GalleryItem $galleryItem): bool
    {
        return false;
    }
}
