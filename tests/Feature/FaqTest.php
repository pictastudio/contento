<?php

use PictaStudio\Contento\Models\{Faq, FaqCategory};

use function Pest\Laravel\{assertDatabaseHas, getJson, postJson};

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
        'translatable_type' => FaqCategory::class,
        'translatable_id' => $category->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'General',
    ]);
})->group('slug-test');

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
        'translatable_type' => Faq::class,
        'translatable_id' => $faq->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'What is this?',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => Faq::class,
        'translatable_id' => $faq->getKey(),
        'locale' => 'en',
        'attribute' => 'content',
        'value' => 'This is a test.',
    ]);
});
