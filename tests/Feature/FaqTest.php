<?php

use PictaStudio\Contento\Models\{Faq, FaqCategory};
use PictaStudio\Translatable\Locales;

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

it('can create a faq category with multiple locale payload', function () {
    config()->set('translatable.locales', ['en', 'it', 'de']);
    app(Locales::class)->load();

    postJson(config('contento.prefix') . '/faq-categories', [
        'en' => ['title' => 'General', 'abstract' => 'General abstract'],
        'it' => ['title' => 'Generale', 'abstract' => 'Sommario breve'],
        'de' => ['title' => 'Allgemein', 'abstract' => 'Kurzer Überblick'],
    ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'General');

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

    assertDatabaseHas('translations', [
        'translatable_type' => $category->getMorphClass(),
        'translatable_id' => $category->getKey(),
        'locale' => 'it',
        'attribute' => 'title',
        'value' => 'Generale',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $category->getMorphClass(),
        'translatable_id' => $category->getKey(),
        'locale' => 'de',
        'attribute' => 'title',
        'value' => 'Allgemein',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $category->getMorphClass(),
        'translatable_id' => $category->getKey(),
        'locale' => 'it',
        'attribute' => 'slug',
        'value' => 'generale',
    ]);
});

it('generates faq category slug from translated titles when default locale title is missing', function () {
    config()->set('translatable.locales', ['en', 'it']);
    app(Locales::class)->load();

    postJson(config('contento.prefix') . '/faq-categories', [
        'it' => ['title' => 'Categoria Generale'],
    ])
        ->assertCreated();

    $category = FaqCategory::query()->firstOrFail();

    assertDatabaseHas(config('contento.table_names.faq_categories'), [
        'id' => $category->getKey(),
        'slug' => 'categoria-generale',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $category->getMorphClass(),
        'translatable_id' => $category->getKey(),
        'locale' => 'it',
        'attribute' => 'title',
        'value' => 'Categoria Generale',
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

it('can create a faq with multiple locale payload', function () {
    config()->set('translatable.locales', ['en', 'it', 'de']);
    app(Locales::class)->load();

    $category = FaqCategory::factory()->create();

    postJson(config('contento.prefix') . '/faqs', [
        'faq_category_id' => $category->getKey(),
        'en' => ['title' => 'What is this?', 'content' => 'English content'],
        'it' => ['title' => 'Che cos\'è?', 'content' => 'Contenuto italiano'],
        'de' => ['title' => 'Was ist das?', 'content' => 'Deutscher Inhalt'],
    ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'What is this?');

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
        'locale' => 'it',
        'attribute' => 'content',
        'value' => 'Contenuto italiano',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $faq->getMorphClass(),
        'translatable_id' => $faq->getKey(),
        'locale' => 'de',
        'attribute' => 'title',
        'value' => 'Was ist das?',
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

    assertDatabaseHas('translations', [
        'translatable_type' => $faq->getMorphClass(),
        'translatable_id' => $faq->getKey(),
        'locale' => 'en',
        'attribute' => 'slug',
        'value' => 'english-title',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $faq->getMorphClass(),
        'translatable_id' => $faq->getKey(),
        'locale' => 'it',
        'attribute' => 'slug',
        'value' => 'titolo-italiano-aggiornato',
    ]);
});

it('stores faq title and slug translations on create and update across locales', function () {
    config()->set('translatable.locales', ['en', 'it', 'de']);
    app(Locales::class)->load();

    $category = FaqCategory::factory()->create();

    postJson(config('contento.prefix') . '/faqs', [
        'faq_category_id' => $category->getKey(),
        'en' => ['title' => 'How does it work?', 'content' => 'English answer'],
        'it' => ['title' => 'Come funziona?', 'content' => 'Risposta italiana'],
    ])->assertCreated();

    $faq = Faq::query()->firstOrFail();

    assertDatabaseHas('translations', [
        'translatable_type' => $faq->getMorphClass(),
        'translatable_id' => $faq->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'How does it work?',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $faq->getMorphClass(),
        'translatable_id' => $faq->getKey(),
        'locale' => 'en',
        'attribute' => 'slug',
        'value' => 'how-does-it-work',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $faq->getMorphClass(),
        'translatable_id' => $faq->getKey(),
        'locale' => 'it',
        'attribute' => 'title',
        'value' => 'Come funziona?',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $faq->getMorphClass(),
        'translatable_id' => $faq->getKey(),
        'locale' => 'it',
        'attribute' => 'slug',
        'value' => 'come-funziona',
    ]);

    putJson(config('contento.prefix') . '/faqs/' . $faq->getKey(), [
        'faq_category_id' => $category->getKey(),
        'de' => ['title' => 'Wie funktioniert es?', 'content' => 'Deutsche Antwort'],
    ])->assertOk();

    assertDatabaseHas('translations', [
        'translatable_type' => $faq->getMorphClass(),
        'translatable_id' => $faq->getKey(),
        'locale' => 'de',
        'attribute' => 'title',
        'value' => 'Wie funktioniert es?',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $faq->getMorphClass(),
        'translatable_id' => $faq->getKey(),
        'locale' => 'de',
        'attribute' => 'slug',
        'value' => 'wie-funktioniert-es',
    ]);
});
