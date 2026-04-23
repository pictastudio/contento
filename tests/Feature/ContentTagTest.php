<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PictaStudio\Contento\Actions\ContentTags\CreateContentTag;
use PictaStudio\Contento\Models\{ContentTag, Faq, FaqCategory, MailForm, Modal, Page};
use PictaStudio\Translatable\Locales;

use function Pest\Laravel\{assertDatabaseHas, getJson, patchJson, post, postJson};

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

    expect($contentTag->images)->toHaveCount(3)
        ->and(str_starts_with((string) data_get($thumb, 'src'), 'content_tags/' . $contentTag->getKey() . '/thumb/'))->toBeTrue()
        ->and(str_starts_with((string) data_get($genericImage, 'src'), 'content_tags/' . $contentTag->getKey() . '/images/'))->toBeTrue();

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

    ContentTag::factory()->create([
        'name' => 'Root A Child Early',
        'parent_id' => $rootA->getKey(),
        'sort_order' => 5,
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
        ->assertJsonPath(contentoCollectionPath('1.children.1.name'), 'Root A Child Late');
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
