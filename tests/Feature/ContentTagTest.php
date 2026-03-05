<?php

use PictaStudio\Contento\Models\{ContentTag, Faq, FaqCategory, MailForm, Modal, Page};
use PictaStudio\Translatable\Locales;

use function Pest\Laravel\{assertDatabaseHas, getJson, patchJson, postJson};

it('can list content tags', function () {
    ContentTag::factory()->count(3)->create();

    getJson(config('contento.prefix') . '/content-tags')
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

    getJson(config('contento.prefix') . '/content-tags?' . $query)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $third->getKey())
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 2);
});

it('rejects unsupported content tag list query params', function () {
    getJson(config('contento.prefix') . '/content-tags?unknown=1')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unknown']);
});

it('can create a content tag', function () {
    postJson(config('contento.prefix') . '/content-tags', [
        'name' => 'Summer',
        'abstract' => 'Abstract text',
        'description' => 'Description text',
        'sort_order' => 1,
    ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Summer');

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

    postJson(config('contento.prefix') . '/content-tags', [
        'sort_order' => 1,
        'en' => ['name' => 'Summer'],
        'it' => ['name' => 'Estate', 'description' => 'Descrizione locale'],
        'de' => ['name' => 'Sommer'],
    ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Summer');

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

    getJson(config('contento.prefix') . '/content-tags?as_tree=1')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Root B')
        ->assertJsonPath('data.1.name', 'Root A')
        ->assertJsonPath('data.0.children.0.name', 'Root B Child Early')
        ->assertJsonPath('data.0.children.1.name', 'Root B Child Late')
        ->assertJsonPath('data.1.children.0.name', 'Root A Child Early')
        ->assertJsonPath('data.1.children.1.name', 'Root A Child Late');
});

it('associates content tags polymorphically to content models and other content tags', function () {
    $page = Page::factory()->create();
    $faqCategory = FaqCategory::factory()->create();
    $faq = Faq::factory()->create(['faq_category_id' => $faqCategory->getKey()]);

    $contentTag = ContentTag::factory()->create(['name' => 'Shared']);
    $relatedTag = ContentTag::factory()->create(['name' => 'Related']);

    patchJson(config('contento.prefix') . '/content-tags/' . $contentTag->getKey(), [
        'tag_ids' => [$relatedTag->getKey()],
    ])->assertOk();

    patchJson(config('contento.prefix') . '/pages/' . $page->getKey(), [
        'tag_ids' => [$contentTag->getKey()],
    ])->assertOk();

    patchJson(config('contento.prefix') . '/faq-categories/' . $faqCategory->getKey(), [
        'tag_ids' => [$contentTag->getKey()],
    ])->assertOk();

    patchJson(config('contento.prefix') . '/faqs/' . $faq->getKey(), [
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

    patchJson(config('contento.prefix') . '/content-tags/' . $parent->getKey(), [
        'parent_id' => $child->getKey(),
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['parent_id']);

    patchJson(config('contento.prefix') . '/content-tags/' . $parent->getKey(), [
        'tag_ids' => [$parent->getKey()],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['tag_ids']);
});

it('does not allow tagging mail forms and modals', function () {
    $mailForm = MailForm::factory()->create();
    $modal = Modal::factory()->create();
    $contentTag = ContentTag::factory()->create();

    patchJson(config('contento.prefix') . '/mail-forms/' . $mailForm->getKey(), [
        'tag_ids' => [$contentTag->getKey()],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['tag_ids']);

    patchJson(config('contento.prefix') . '/modals/' . $modal->getKey(), [
        'tag_ids' => [$contentTag->getKey()],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['tag_ids']);
});
