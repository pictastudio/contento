<?php

use PictaStudio\Contento\Models\{Faq, FaqCategory};
use PictaStudio\Translatable\Locales;

use function Pest\Laravel\{assertDatabaseHas, getJson, postJson, putJson};

it('can list faq categories', function () {
    FaqCategory::factory()->count(2)->create();

    getJson(config('contento.routes.api.v1.prefix') . '/faq-categories')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('can filter, sort and paginate faq categories', function () {
    $first = FaqCategory::factory()->create(['active' => true]);
    FaqCategory::factory()->create(['active' => false]);
    $third = FaqCategory::factory()->create(['active' => true]);

    $query = http_build_query([
        'id' => [$first->getKey(), $third->getKey()],
        'is_active' => 1,
        'sort_by' => 'id',
        'sort_dir' => 'desc',
        'per_page' => 1,
        'page' => 1,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/faq-categories?' . $query)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $third->getKey())
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 2);
});

it('rejects unsupported faq category list query params', function () {
    getJson(config('contento.routes.api.v1.prefix') . '/faq-categories?unknown=1')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unknown']);
});

it('can create a faq category', function () {
    postJson(config('contento.routes.api.v1.prefix') . '/faq-categories', [
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

    putJson(config('contento.routes.api.v1.prefix') . '/faq-categories/' . $category->getKey(), [
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

    postJson(config('contento.routes.api.v1.prefix') . '/faq-categories', [
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

    postJson(config('contento.routes.api.v1.prefix') . '/faq-categories', [
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

    getJson(config('contento.routes.api.v1.prefix') . '/faqs')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('can filter, sort and paginate faqs', function () {
    $category = FaqCategory::factory()->create();
    $first = Faq::factory()->create(['faq_category_id' => $category->getKey(), 'active' => true]);
    Faq::factory()->create(['faq_category_id' => $category->getKey(), 'active' => false]);
    $third = Faq::factory()->create(['faq_category_id' => $category->getKey(), 'active' => true]);

    $query = http_build_query([
        'id' => [$first->getKey(), $third->getKey()],
        'faq_category_id' => $category->getKey(),
        'is_active' => 1,
        'sort_by' => 'id',
        'sort_dir' => 'desc',
        'per_page' => 1,
        'page' => 1,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/faqs?' . $query)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $third->getKey())
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 2);
});

it('rejects unsupported faq list query params', function () {
    getJson(config('contento.routes.api.v1.prefix') . '/faqs?unknown=1')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unknown']);
});

it('can create a faq', function () {
    $category = FaqCategory::factory()->create();

    postJson(config('contento.routes.api.v1.prefix') . '/faqs', [
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

    postJson(config('contento.routes.api.v1.prefix') . '/faqs', [
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

    putJson(config('contento.routes.api.v1.prefix') . '/faqs/' . $faq->getKey(), [
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

it('bulk upserts faqs by updating existing records and creating new ones', function () {
    config()->set('translatable.locales', ['en', 'it']);
    app(Locales::class)->load();

    $category = FaqCategory::factory()->create();
    $existingFaq = Faq::factory()->create([
        'faq_category_id' => $category->getKey(),
        'title' => 'Old title',
        'content' => 'Old content',
    ]);

    postJson(config('contento.routes.api.v1.prefix') . '/faqs/bulk/upsert', [
        'faqs' => [
            [
                'id' => $existingFaq->getKey(),
                'content' => 'Updated content',
                'active' => false,
            ],
            [
                'faq_category_id' => $category->getKey(),
                'en' => [
                    'title' => 'How does bulk work?',
                    'content' => 'English answer',
                ],
                'it' => [
                    'title' => 'Come funziona il bulk?',
                    'content' => 'Risposta italiana',
                ],
            ],
        ],
    ])
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $existingFaq->getKey())
        ->assertJsonPath('data.0.content', 'Updated content')
        ->assertJsonPath('data.0.active', false)
        ->assertJsonPath('data.1.title', 'How does bulk work?');

    $createdFaq = Faq::query()
        ->where('title', 'How does bulk work?')
        ->firstOrFail();

    assertDatabaseHas(config('contento.table_names.faqs'), [
        'id' => $existingFaq->getKey(),
        'content' => 'Updated content',
        'active' => false,
    ]);

    assertDatabaseHas(config('contento.table_names.faqs'), [
        'id' => $createdFaq->getKey(),
        'faq_category_id' => $category->getKey(),
        'slug' => 'how-does-bulk-work',
        'content' => 'English answer',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $createdFaq->getMorphClass(),
        'translatable_id' => $createdFaq->getKey(),
        'locale' => 'it',
        'attribute' => 'title',
        'value' => 'Come funziona il bulk?',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $createdFaq->getMorphClass(),
        'translatable_id' => $createdFaq->getKey(),
        'locale' => 'it',
        'attribute' => 'slug',
        'value' => 'come-funziona-il-bulk',
    ]);
});

it('bulk upserts faqs from a raw array payload', function () {
    $category = FaqCategory::factory()->create();

    postJson(config('contento.routes.api.v1.prefix') . '/faqs/bulk/upsert', [
        [
            'faq_category_id' => $category->getKey(),
            'title' => 'Raw payload faq',
            'content' => 'Created from a raw array payload.',
        ],
    ])
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Raw payload faq');

    assertDatabaseHas(config('contento.table_names.faqs'), [
        'title' => 'Raw payload faq',
        'slug' => 'raw-payload-faq',
    ]);
});

it('validates duplicate ids and missing update targets in faq bulk upserts', function () {
    $existingFaq = Faq::factory()->create();
    $missingFaqId = $existingFaq->getKey() + 999;

    postJson(config('contento.routes.api.v1.prefix') . '/faqs/bulk/upsert', [
        'faqs' => [
            [
                'id' => $existingFaq->getKey(),
                'content' => 'First update',
            ],
            [
                'id' => $existingFaq->getKey(),
                'content' => 'Duplicate update',
            ],
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['faqs.1.id']);

    postJson(config('contento.routes.api.v1.prefix') . '/faqs/bulk/upsert', [
        'faqs' => [
            [
                'id' => $missingFaqId,
                'content' => 'Missing update target',
            ],
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['faqs']);
});

it('can update translations for multiple locales on a faq', function () {
    $category = FaqCategory::factory()->create();

    app()->setLocale('en');
    postJson(config('contento.routes.api.v1.prefix') . '/faqs', [
        'faq_category_id' => $category->getKey(),
        'title' => 'Initial title',
        'content' => 'Initial content',
    ])->assertCreated();

    $faq = Faq::query()->firstOrFail();

    app()->setLocale('en');
    putJson(config('contento.routes.api.v1.prefix') . '/faqs/' . $faq->getKey(), [
        'faq_category_id' => $category->getKey(),
        'title' => 'English title',
        'content' => 'English content',
    ])->assertOk();

    app()->setLocale('it');
    putJson(config('contento.routes.api.v1.prefix') . '/faqs/' . $faq->getKey(), [
        'faq_category_id' => $category->getKey(),
        'title' => 'Titolo italiano',
        'content' => 'Contenuto italiano',
    ])->assertOk();

    putJson(config('contento.routes.api.v1.prefix') . '/faqs/' . $faq->getKey(), [
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

    postJson(config('contento.routes.api.v1.prefix') . '/faqs', [
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

    putJson(config('contento.routes.api.v1.prefix') . '/faqs/' . $faq->getKey(), [
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
