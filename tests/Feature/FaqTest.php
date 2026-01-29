<?php

use PictaStudio\Contento\Models\{Faq, FaqCategory};

use function Pest\Laravel\{assertDatabaseHas, getJson, postJson};

it('can list faq categories', function () {
    FaqCategory::factory()->count(2)->create();

    getJson(config('contento.prefix') . '/faq-categories')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('can create an faq category', function () {
    postJson(config('contento.prefix') . '/faq-categories', [
        'title' => 'General',
        'slug' => 'general',
    ])
        ->assertCreated();

    assertDatabaseHas(config('contento.table_names.faq_categories'), [
        'title' => 'General',
    ]);
});

it('can list faqs', function () {
    Faq::factory()->count(3)->create();

    getJson(config('contento.prefix') . '/faqs')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('can create an faq', function () {
    $category = FaqCategory::factory()->create();

    postJson(config('contento.prefix') . '/faqs', [
        'faq_category_id' => $category->id,
        'title' => 'What is this?',
        'slug' => 'what-is-this',
        'content' => 'This is a test.',
    ])
        ->assertCreated();

    assertDatabaseHas(config('contento.table_names.faqs'), [
        'title' => 'What is this?',
    ]);
});
