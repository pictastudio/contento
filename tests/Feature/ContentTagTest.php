<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PictaStudio\Contento\Actions\ContentTags\CreateContentTag;
use PictaStudio\Contento\Actions\Tree\RebuildTreePaths;
use PictaStudio\Contento\Models\{ContentTag, Faq, FaqCategory, MailForm, Modal, Page};
use PictaStudio\Translatable\Locales;

use function Pest\Laravel\{assertDatabaseHas, assertDatabaseMissing, deleteJson, getJson, patchJson, post, postJson};

it('can list content tags', function () {
    ContentTag::factory()->count(3)->create();

    getJson(config('contento.routes.api.v1.prefix') . '/content-tags')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('can filter, sort and paginate content tags', function () {
    $first = ContentTag::factory()->create(['active' => true, 'sort_order' => 10]);
    ContentTag::factory()->create(['active' => false, 'sort_order' => 5]);
    $third = ContentTag::factory()->create(['active' => true, 'sort_order' => 30]);

    $query = http_build_query([
        'id' => [$first->getKey(), $third->getKey()],
        'is_active' => 1,
        'sort_by' => 'sort_order',
        'sort_dir' => 'desc',
        'per_page' => 1,
        'page' => 1,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/content-tags?' . $query)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $third->getKey())
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 2);
});

it('orders content tags by sort order by default', function () {
    $lowest = ContentTag::factory()->create(['sort_order' => 5]);
    $middle = ContentTag::factory()->create(['sort_order' => 10]);
    $highest = ContentTag::factory()->create(['sort_order' => 20]);

    getJson(config('contento.routes.api.v1.prefix') . '/content-tags')
        ->assertOk()
        ->assertJsonPath('data.0.id', $lowest->getKey())
        ->assertJsonPath('data.1.id', $middle->getKey())
        ->assertJsonPath('data.2.id', $highest->getKey());
});

it('can list all content tags with the all filter', function () {
    $visible = ContentTag::factory()->create([
        'active' => true,
        'visible_from' => now()->subDay(),
        'visible_until' => now()->addDay(),
    ]);
    $inactive = ContentTag::factory()->create([
        'active' => false,
        'visible_from' => now()->subDay(),
        'visible_until' => now()->addDay(),
    ]);
    $future = ContentTag::factory()->create([
        'active' => true,
        'visible_from' => now()->addDay(),
        'visible_until' => null,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/content-tags?all=1&per_page=1&sort_by=id&sort_dir=asc')
        ->assertOk()
        ->assertJsonCount(3, contentoCollectionPath())
        ->assertJsonPath(contentoCollectionPath('0.id'), $visible->getKey())
        ->assertJsonPath(contentoCollectionPath('1.id'), $inactive->getKey())
        ->assertJsonPath(contentoCollectionPath('2.id'), $future->getKey())
        ->assertJsonMissingPath('meta')
        ->assertJsonMissingPath('links');

    getJson(config('contento.routes.api.v1.prefix') . '/content-tags?filter=all&per_page=1&sort_by=id&sort_dir=asc')
        ->assertOk()
        ->assertJsonCount(3, contentoCollectionPath())
        ->assertJsonPath(contentoCollectionPath('0.id'), $visible->getKey())
        ->assertJsonPath(contentoCollectionPath('1.id'), $inactive->getKey())
        ->assertJsonPath(contentoCollectionPath('2.id'), $future->getKey())
        ->assertJsonMissingPath('meta')
        ->assertJsonMissingPath('links');
});

it('rejects unsupported content tag list query params', function () {
    getJson(config('contento.routes.api.v1.prefix') . '/content-tags?unknown=1')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unknown']);
});

it('can create a content tag', function () {
    postJson(config('contento.routes.api.v1.prefix') . '/content-tags', [
        'name' => 'Summer',
        'abstract' => 'Abstract text',
        'description' => 'Description text',
        'sort_order' => 1,
    ])
        ->assertCreated()
        ->assertJsonPath(contentoResourcePath('name'), 'Summer');

    $contentTag = ContentTag::query()->firstOrFail();

    assertDatabaseHas(config('contento.table_names.content_tags'), [
        'id' => $contentTag->getKey(),
        'slug' => 'summer',
        'sort_order' => 1,
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $contentTag->getMorphClass(),
        'translatable_id' => $contentTag->getKey(),
        'locale' => 'en',
        'attribute' => 'name',
        'value' => 'Summer',
    ]);
});

it('can create a content tag with multiple locale payload', function () {
    config()->set('translatable.locales', ['en', 'it', 'de']);
    app(Locales::class)->load();

    postJson(config('contento.routes.api.v1.prefix') . '/content-tags', [
        'sort_order' => 1,
        'en' => ['name' => 'Summer'],
        'it' => ['name' => 'Estate', 'description' => 'Descrizione locale'],
        'de' => ['name' => 'Sommer'],
    ])
        ->assertCreated()
        ->assertJsonPath(contentoResourcePath('name'), 'Summer');

    $contentTag = ContentTag::query()->firstOrFail();

    assertDatabaseHas(config('contento.table_names.content_tags'), [
        'id' => $contentTag->getKey(),
        'slug' => 'summer',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $contentTag->getMorphClass(),
        'translatable_id' => $contentTag->getKey(),
        'locale' => 'it',
        'attribute' => 'name',
        'value' => 'Estate',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $contentTag->getMorphClass(),
        'translatable_id' => $contentTag->getKey(),
        'locale' => 'it',
        'attribute' => 'slug',
        'value' => 'estate',
    ]);
});

it('requires content tag sort_order to start at one for api writes', function () {
    postJson(config('contento.routes.api.v1.prefix') . '/content-tags', [
        'name' => 'Invalid order',
        'sort_order' => 0,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['sort_order']);

    $contentTag = ContentTag::factory()->create([
        'sort_order' => 1,
    ]);

    patchJson(config('contento.routes.api.v1.prefix') . '/content-tags/' . $contentTag->getKey(), [
        'sort_order' => 0,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['sort_order']);

    patchJson(config('contento.routes.api.v1.prefix') . '/content-tags/bulk/update', [
        'content_tags' => [
            [
                'id' => $contentTag->getKey(),
                'parent_id' => null,
                'sort_order' => 0,
            ],
        ],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['content_tags.0.sort_order']);
});

it('stores content tag images as a catalog images collection', function () {
    Storage::fake('public');

    $response = post(
        config('contento.routes.api.v1.prefix') . '/content-tags',
        [
            'name' => 'Visual content tag',
            'sort_order' => 1,
            'images' => [
                [
                    'file' => UploadedFile::fake()->image('thumb.jpg'),
                    'type' => 'thumb',
                    'alt' => 'Thumb',
                ],
                [
                    'file' => UploadedFile::fake()->image('gallery-a.jpg'),
                    'type' => null,
                    'alt' => 'Gallery A',
                    'sort_order' => 10,
                ],
                [
                    'file' => UploadedFile::fake()->image('gallery-b.jpg'),
                    'alt' => 'Gallery B',
                    'sort_order' => 20,
                ],
            ],
        ],
        ['Accept' => 'application/json']
    )->assertCreated()
        ->assertJsonCount(3, contentoResourcePath('images'))
        ->assertJsonPath(contentoResourcePath('images.0.type'), 'thumb')
        ->assertJsonPath(contentoResourcePath('images.1.type'), null)
        ->assertJsonPath(contentoResourcePath('images.2.type'), null);

    $contentTag = ContentTag::query()->findOrFail($response->json(contentoResourcePath('id')));
    $thumb = collect($contentTag->images)->firstWhere('type', 'thumb');
    $genericImage = collect($contentTag->images)->firstWhere('type', null);
    $datePath = now()->format('Y/m/d');

    expect($contentTag->images)->toHaveCount(3)
        ->and(str_starts_with(
            (string) data_get($thumb, 'src'),
            'content_tags/' . $contentTag->getKey() . '/thumb/' . $datePath . '/'
        ))->toBeTrue()
        ->and(str_starts_with(
            (string) data_get($genericImage, 'src'),
            'content_tags/' . $contentTag->getKey() . '/images/' . $datePath . '/'
        ))->toBeTrue();

    Storage::disk('public')->assertExists((string) data_get($thumb, 'src'));
    Storage::disk('public')->assertExists((string) data_get($genericImage, 'src'));
});

it('updates content tag image metadata without requiring a new upload', function () {
    $contentTag = ContentTag::factory()->create([
        'images' => [
            [
                'id' => 'generic-image',
                'type' => null,
                'alt' => 'Old alt',
                'mimetype' => 'image/jpeg',
                'sort_order' => 10,
                'src' => 'content_tags/generic.jpg',
            ],
        ],
    ]);

    patchJson(config('contento.routes.api.v1.prefix') . '/content-tags/' . $contentTag->getKey(), [
        'images' => [
            [
                'id' => 'generic-image',
                'alt' => 'Updated alt',
                'sort_order' => 2,
            ],
        ],
    ])->assertOk()
        ->assertJsonPath(contentoResourcePath('images.0.id'), 'generic-image')
        ->assertJsonPath(contentoResourcePath('images.0.type'), null)
        ->assertJsonPath(contentoResourcePath('images.0.alt'), 'Updated alt')
        ->assertJsonPath(contentoResourcePath('images.0.sort_order'), 2);
});

it('rejects more than one thumb or cover image per content tag payload', function () {
    Storage::fake('public');

    post(
        config('contento.routes.api.v1.prefix') . '/content-tags',
        [
            'name' => 'Duplicate visual tag',
            'sort_order' => 1,
            'images' => [
                [
                    'file' => UploadedFile::fake()->image('cover-a.jpg'),
                    'type' => 'cover',
                ],
                [
                    'file' => UploadedFile::fake()->image('cover-b.jpg'),
                    'type' => 'cover',
                ],
            ],
        ],
        ['Accept' => 'application/json']
    )->assertUnprocessable()
        ->assertJsonValidationErrors(['images.1.type']);
});

it('rejects moving a content tag image to a typed slot already in use', function () {
    $contentTag = ContentTag::factory()->create([
        'images' => [
            [
                'id' => 'thumb-image',
                'type' => 'thumb',
                'src' => 'content_tags/thumb.jpg',
                'sort_order' => 0,
            ],
            [
                'id' => 'generic-image',
                'type' => null,
                'src' => 'content_tags/generic.jpg',
                'sort_order' => 1,
            ],
        ],
    ]);

    patchJson(config('contento.routes.api.v1.prefix') . '/content-tags/' . $contentTag->getKey(), [
        'images' => [
            [
                'id' => 'generic-image',
                'type' => 'thumb',
            ],
        ],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['images.0.type']);
});

it('returns content tags as tree when as_tree is enabled', function () {
    $rootA = ContentTag::factory()->create([
        'name' => 'Root A',
        'sort_order' => 20,
    ]);

    $rootB = ContentTag::factory()->create([
        'name' => 'Root B',
        'sort_order' => 10,
    ]);

    ContentTag::factory()->create([
        'name' => 'Root A Child Late',
        'parent_id' => $rootA->getKey(),
        'sort_order' => 30,
    ]);

    $rootAChildEarly = ContentTag::factory()->create([
        'name' => 'Root A Child Early',
        'parent_id' => $rootA->getKey(),
        'sort_order' => 5,
    ]);

    ContentTag::factory()->create([
        'name' => 'Root A Grandchild Late',
        'parent_id' => $rootAChildEarly->getKey(),
        'sort_order' => 2,
    ]);

    ContentTag::factory()->create([
        'name' => 'Root A Grandchild Early',
        'parent_id' => $rootAChildEarly->getKey(),
        'sort_order' => 1,
    ]);

    ContentTag::factory()->create([
        'name' => 'Root B Child Late',
        'parent_id' => $rootB->getKey(),
        'sort_order' => 40,
    ]);

    ContentTag::factory()->create([
        'name' => 'Root B Child Early',
        'parent_id' => $rootB->getKey(),
        'sort_order' => 1,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/content-tags?as_tree=1')
        ->assertOk()
        ->assertJsonPath(contentoCollectionPath('0.name'), 'Root B')
        ->assertJsonPath(contentoCollectionPath('1.name'), 'Root A')
        ->assertJsonPath(contentoCollectionPath('0.children.0.name'), 'Root B Child Early')
        ->assertJsonPath(contentoCollectionPath('0.children.1.name'), 'Root B Child Late')
        ->assertJsonPath(contentoCollectionPath('1.children.0.name'), 'Root A Child Early')
        ->assertJsonPath(contentoCollectionPath('1.children.1.name'), 'Root A Child Late')
        ->assertJsonPath(contentoCollectionPath('1.children.0.children.0.name'), 'Root A Grandchild Early')
        ->assertJsonPath(contentoCollectionPath('1.children.0.children.1.name'), 'Root A Grandchild Late');
});

it('stores and rebuilds content tag paths when deleting a parent tag', function () {
    $root = ContentTag::factory()->create([
        'name' => 'Root',
        'sort_order' => 1,
    ]);
    $child = ContentTag::factory()->create([
        'name' => 'Child',
        'parent_id' => $root->getKey(),
        'sort_order' => 2,
    ]);
    $grandChild = ContentTag::factory()->create([
        'name' => 'Grandchild',
        'parent_id' => $child->getKey(),
        'sort_order' => 3,
    ]);

    app(RebuildTreePaths::class)->rebuild($root);

    expect((string) $child->fresh()->path)->toBe($root->getKey() . '.' . $child->getKey())
        ->and((string) $grandChild->fresh()->path)->toBe(
            $root->getKey() . '.' . $child->getKey() . '.' . $grandChild->getKey()
        );

    deleteJson(config('contento.routes.api.v1.prefix') . '/content-tags/' . $root->getKey())
        ->assertNoContent();

    $child->refresh();
    $grandChild->refresh();

    expect($child->parent_id)->toBeNull()
        ->and((string) $child->path)->toBe((string) $child->getKey())
        ->and((string) $grandChild->path)->toBe($child->getKey() . '.' . $grandChild->getKey());

    getJson(config('contento.routes.api.v1.prefix') . '/content-tags?as_tree=1')
        ->assertOk()
        ->assertJsonPath(contentoCollectionPath('0.id'), $child->getKey())
        ->assertJsonPath(contentoCollectionPath('0.children.0.id'), $grandChild->getKey());
});

it('promotes content tag children to the deleted tag parent by default', function () {
    $root = ContentTag::factory()->create([
        'name' => 'Root',
        'sort_order' => 1,
    ]);
    $middle = ContentTag::factory()->create([
        'name' => 'Middle',
        'parent_id' => $root->getKey(),
        'sort_order' => 2,
    ]);
    $child = ContentTag::factory()->create([
        'name' => 'Child',
        'parent_id' => $middle->getKey(),
        'sort_order' => 3,
    ]);
    $grandChild = ContentTag::factory()->create([
        'name' => 'Grandchild',
        'parent_id' => $child->getKey(),
        'sort_order' => 4,
    ]);

    app(RebuildTreePaths::class)->rebuild($root);

    deleteJson(config('contento.routes.api.v1.prefix') . '/content-tags/' . $middle->getKey())
        ->assertNoContent();

    $child->refresh();
    $grandChild->refresh();

    assertDatabaseMissing('content_tags', ['id' => $middle->getKey()]);

    expect($child->parent_id)->toBe($root->getKey())
        ->and((string) $child->path)->toBe($root->getKey() . '.' . $child->getKey())
        ->and((string) $grandChild->path)->toBe($root->getKey() . '.' . $child->getKey() . '.' . $grandChild->getKey());

    getJson(config('contento.routes.api.v1.prefix') . '/content-tags?as_tree=1')
        ->assertOk()
        ->assertJsonPath(contentoCollectionPath('0.id'), $root->getKey())
        ->assertJsonPath(contentoCollectionPath('0.children.0.id'), $child->getKey())
        ->assertJsonPath(contentoCollectionPath('0.children.0.children.0.id'), $grandChild->getKey());
});

it('recursively deletes content tag children when requested and clears tag associations', function () {
    $parent = ContentTag::factory()->create();
    $child = ContentTag::factory()->create([
        'parent_id' => $parent->getKey(),
    ]);
    $grandChild = ContentTag::factory()->create([
        'parent_id' => $child->getKey(),
    ]);
    $relatedTag = ContentTag::factory()->create();

    $child->contentTags()->sync([$relatedTag->getKey()]);
    $relatedTag->contentTags()->sync([$grandChild->getKey()]);

    deleteJson(config('contento.routes.api.v1.prefix') . '/content-tags/' . $parent->getKey() . '?delete_children=1')
        ->assertNoContent();

    assertDatabaseMissing('content_tags', ['id' => $parent->getKey()]);
    assertDatabaseMissing('content_tags', ['id' => $child->getKey()]);
    assertDatabaseMissing('content_tags', ['id' => $grandChild->getKey()]);
    assertDatabaseMissing('content_taggables', [
        'taggable_type' => $child->getMorphClass(),
        'taggable_id' => $child->getKey(),
    ]);
    assertDatabaseMissing('content_taggables', [
        'content_tag_id' => $grandChild->getKey(),
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/content-tags?as_tree=1')
        ->assertOk()
        ->assertJsonCount(1, contentoCollectionPath())
        ->assertJsonPath(contentoCollectionPath('0.id'), $relatedTag->getKey());
});

it('bulk updates content tag parent_id and sort_order', function () {
    $root = ContentTag::factory()->create([
        'name' => 'Root',
        'sort_order' => 1,
    ]);

    $firstChild = ContentTag::factory()->create([
        'name' => 'First Child',
        'parent_id' => $root->getKey(),
        'sort_order' => 10,
    ]);

    $secondChild = ContentTag::factory()->create([
        'name' => 'Second Child',
        'parent_id' => null,
        'sort_order' => 20,
    ]);

    patchJson(config('contento.routes.api.v1.prefix') . '/content-tags/bulk/update', [
        'content_tags' => [
            [
                'id' => $firstChild->getKey(),
                'parent_id' => $root->getKey(),
                'sort_order' => 30,
            ],
            [
                'id' => $secondChild->getKey(),
                'parent_id' => $root->getKey(),
                'sort_order' => 5,
            ],
        ],
    ])
        ->assertOk()
        ->assertJsonPath(contentoCollectionPath('0.id'), $secondChild->getKey())
        ->assertJsonPath(contentoCollectionPath('0.parent_id'), $root->getKey())
        ->assertJsonPath(contentoCollectionPath('0.sort_order'), 5)
        ->assertJsonPath(contentoCollectionPath('1.id'), $firstChild->getKey())
        ->assertJsonPath(contentoCollectionPath('1.sort_order'), 30);

    getJson(config('contento.routes.api.v1.prefix') . '/content-tags?as_tree=1')
        ->assertOk()
        ->assertJsonPath(contentoCollectionPath('0.name'), 'Root')
        ->assertJsonPath(contentoCollectionPath('0.children.0.name'), 'Second Child')
        ->assertJsonPath(contentoCollectionPath('0.children.1.name'), 'First Child');

    assertDatabaseHas(config('contento.table_names.content_tags'), [
        'id' => $secondChild->getKey(),
        'parent_id' => $root->getKey(),
        'sort_order' => 5,
    ]);
});

it('prevents circular references in bulk content tag updates', function () {
    $firstTag = ContentTag::factory()->create([
        'sort_order' => 1,
    ]);

    $secondTag = ContentTag::factory()->create([
        'parent_id' => $firstTag->getKey(),
        'sort_order' => 2,
    ]);

    patchJson(config('contento.routes.api.v1.prefix') . '/content-tags/bulk/update', [
        'content_tags' => [
            [
                'id' => $firstTag->getKey(),
                'parent_id' => $secondTag->getKey(),
                'sort_order' => 10,
            ],
            [
                'id' => $secondTag->getKey(),
                'parent_id' => $firstTag->getKey(),
                'sort_order' => 20,
            ],
        ],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['content_tags.0.parent_id', 'content_tags.1.parent_id']);
});

it('associates content tags polymorphically to content models and other content tags', function () {
    $page = Page::factory()->create();
    $faqCategory = FaqCategory::factory()->create();
    $faq = Faq::factory()->create(['faq_category_id' => $faqCategory->getKey()]);

    $contentTag = ContentTag::factory()->create(['name' => 'Shared']);
    $relatedTag = ContentTag::factory()->create(['name' => 'Related']);

    patchJson(config('contento.routes.api.v1.prefix') . '/content-tags/' . $contentTag->getKey(), [
        'tag_ids' => [$relatedTag->getKey()],
    ])->assertOk();

    patchJson(config('contento.routes.api.v1.prefix') . '/pages/' . $page->getKey(), [
        'tag_ids' => [$contentTag->getKey()],
    ])->assertOk();

    patchJson(config('contento.routes.api.v1.prefix') . '/faq-categories/' . $faqCategory->getKey(), [
        'tag_ids' => [$contentTag->getKey()],
    ])->assertOk();

    patchJson(config('contento.routes.api.v1.prefix') . '/faqs/' . $faq->getKey(), [
        'tag_ids' => [$contentTag->getKey()],
    ])->assertOk();

    assertDatabaseHas(config('contento.table_names.content_taggables'), [
        'content_tag_id' => $relatedTag->getKey(),
        'taggable_type' => $contentTag->getMorphClass(),
        'taggable_id' => $contentTag->getKey(),
    ]);

    assertDatabaseHas(config('contento.table_names.content_taggables'), [
        'content_tag_id' => $contentTag->getKey(),
        'taggable_type' => $page->getMorphClass(),
        'taggable_id' => $page->getKey(),
    ]);

    assertDatabaseHas(config('contento.table_names.content_taggables'), [
        'content_tag_id' => $contentTag->getKey(),
        'taggable_type' => $faqCategory->getMorphClass(),
        'taggable_id' => $faqCategory->getKey(),
    ]);

    assertDatabaseHas(config('contento.table_names.content_taggables'), [
        'content_tag_id' => $contentTag->getKey(),
        'taggable_type' => $faq->getMorphClass(),
        'taggable_id' => $faq->getKey(),
    ]);
});

it('prevents invalid parent and self association updates', function () {
    $parent = ContentTag::factory()->create(['sort_order' => 1]);
    $child = ContentTag::factory()->create([
        'parent_id' => $parent->getKey(),
        'sort_order' => 2,
    ]);

    patchJson(config('contento.routes.api.v1.prefix') . '/content-tags/' . $parent->getKey(), [
        'parent_id' => $child->getKey(),
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['parent_id']);

    patchJson(config('contento.routes.api.v1.prefix') . '/content-tags/' . $parent->getKey(), [
        'tag_ids' => [$parent->getKey()],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['tag_ids']);
});

it('rolls back content tag creation when tag relations are invalid', function () {
    expect(fn () => app(CreateContentTag::class)->handle([
        'name' => 'Self linked',
        'sort_order' => 1,
        'tag_ids' => [1],
    ]))->toThrow(ValidationException::class);

    expect(ContentTag::query()->count())->toBe(0);
});

it('rolls back content tag updates when tag relations are invalid', function () {
    $tag = ContentTag::factory()->create([
        'name' => 'Original name',
        'sort_order' => 1,
    ]);

    patchJson(config('contento.routes.api.v1.prefix') . '/content-tags/' . $tag->getKey(), [
        'name' => 'Changed name',
        'sort_order' => 99,
        'tag_ids' => [$tag->getKey()],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['tag_ids']);

    assertDatabaseHas(config('contento.table_names.content_tags'), [
        'id' => $tag->getKey(),
        'name' => 'Original name',
        'sort_order' => 1,
    ]);
});

it('does not allow tagging mail forms and modals', function () {
    $mailForm = MailForm::factory()->create();
    $modal = Modal::factory()->create();
    $contentTag = ContentTag::factory()->create();

    patchJson(config('contento.routes.api.v1.prefix') . '/mail-forms/' . $mailForm->getKey(), [
        'tag_ids' => [$contentTag->getKey()],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['tag_ids']);

    patchJson(config('contento.routes.api.v1.prefix') . '/modals/' . $modal->getKey(), [
        'tag_ids' => [$contentTag->getKey()],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['tag_ids']);
});
