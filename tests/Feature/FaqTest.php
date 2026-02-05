<?php

use PictaStudio\Contento\Models\{Faq, FaqCategory};

use function Pest\Laravel\{assertDatabaseHas, getJson, postJson, putJson};

it('can list faq categories', function () {
    FaqCategory::factory()->count(2)->create();

    getJson(config('contento.prefix') . '/faq-categories')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('can create a faq category', function () {
    postJson(config('contento.prefix') . '/faq-categories', [
        'title' => 'General',
    ])
        ->assertCreated();

    $category = FaqCategory::query()->firstOrFail();

    assertDatabaseHas(config('contento.table_names.faq_categories'), [
        'id' => $category->getKey(),
        'slug' => 'general',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $category->getMorphClass(),
        'translatable_id' => $category->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'General',
    ]);
})->group('slug-test');

it('can update a faq category', function () {
    $category = FaqCategory::factory()->create([
        'title' => 'General',
        'abstract' => 'Old abstract',
    ]);

    putJson(config('contento.prefix') . '/faq-categories/' . $category->getKey(), [
        'title' => 'Updated Category',
        'abstract' => 'Updated abstract',
    ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated Category');

    assertDatabaseHas(config('contento.table_names.faq_categories'), [
        'id' => $category->getKey(),
        'slug' => 'updated-category',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $category->getMorphClass(),
        'translatable_id' => $category->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'Updated Category',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $category->getMorphClass(),
        'translatable_id' => $category->getKey(),
        'locale' => 'en',
        'attribute' => 'abstract',
        'value' => 'Updated abstract',
    ]);
});

it('can list faqs', function () {
    Faq::factory()->count(3)->create();

    getJson(config('contento.prefix') . '/faqs')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('can create a faq', function () {
    $category = FaqCategory::factory()->create();

    postJson(config('contento.prefix') . '/faqs', [
        'faq_category_id' => $category->id,
        'title' => 'What is this?',
        'content' => 'This is a test.',
    ])
        ->assertCreated();

    $faq = Faq::query()->firstOrFail();

    assertDatabaseHas(config('contento.table_names.faqs'), [
        'id' => $faq->getKey(),
        'slug' => 'what-is-this',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $faq->getMorphClass(),
        'translatable_id' => $faq->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'What is this?',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $faq->getMorphClass(),
        'translatable_id' => $faq->getKey(),
        'locale' => 'en',
        'attribute' => 'content',
        'value' => 'This is a test.',
    ]);
});

it('can update a faq', function () {
    $category = FaqCategory::factory()->create();
    $faq = Faq::factory()->create([
        'faq_category_id' => $category->getKey(),
        'title' => 'Old title',
        'content' => 'Old content',
    ]);

    putJson(config('contento.prefix') . '/faqs/' . $faq->getKey(), [
        'faq_category_id' => $category->getKey(),
        'title' => 'Updated title',
        'content' => 'Updated content',
    ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated title');

    assertDatabaseHas(config('contento.table_names.faqs'), [
        'id' => $faq->getKey(),
        'slug' => 'updated-title',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $faq->getMorphClass(),
        'translatable_id' => $faq->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'Updated title',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $faq->getMorphClass(),
        'translatable_id' => $faq->getKey(),
        'locale' => 'en',
        'attribute' => 'content',
        'value' => 'Updated content',
    ]);
});

it('can update translations for multiple locales on a faq', function () {
    $category = FaqCategory::factory()->create();

    app()->setLocale('en');
    postJson(config('contento.prefix') . '/faqs', [
        'faq_category_id' => $category->getKey(),
        'title' => 'Initial title',
        'content' => 'Initial content',
    ])->assertCreated();

    $faq = Faq::query()->firstOrFail();

    app()->setLocale('en');
    putJson(config('contento.prefix') . '/faqs/' . $faq->getKey(), [
        'faq_category_id' => $category->getKey(),
        'title' => 'English title',
        'content' => 'English content',
    ])->assertOk();

    app()->setLocale('it');
    putJson(config('contento.prefix') . '/faqs/' . $faq->getKey(), [
        'faq_category_id' => $category->getKey(),
        'title' => 'Titolo italiano',
        'content' => 'Contenuto italiano',
    ])->assertOk();

    putJson(config('contento.prefix') . '/faqs/' . $faq->getKey(), [
        'faq_category_id' => $category->getKey(),
        'title' => 'Titolo italiano aggiornato',
        'content' => 'Contenuto italiano aggiornato',
    ])->assertOk();

    assertDatabaseHas('translations', [
        'translatable_type' => $faq->getMorphClass(),
        'translatable_id' => $faq->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'English title',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $faq->getMorphClass(),
        'translatable_id' => $faq->getKey(),
        'locale' => 'it',
        'attribute' => 'title',
        'value' => 'Titolo italiano aggiornato',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $faq->getMorphClass(),
        'translatable_id' => $faq->getKey(),
        'locale' => 'it',
        'attribute' => 'content',
        'value' => 'Contenuto italiano aggiornato',
    ]);
});
