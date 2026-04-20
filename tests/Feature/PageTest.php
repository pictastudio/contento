<?php

use PictaStudio\Contento\Models\Page;
use PictaStudio\Translatable\Locales;

use function Pest\Laravel\{assertDatabaseHas, assertDatabaseMissing, deleteJson, getJson, postJson, putJson};

it('can list pages', function () {
    Page::factory()->count(3)->create();

    getJson(config('contento.routes.api.v1.prefix') . '/pages')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('can filter, sort and paginate pages', function () {
    $first = Page::factory()->create(['active' => true, 'type' => 'news']);
    Page::factory()->create(['active' => false, 'type' => 'news']);
    $third = Page::factory()->create(['active' => true, 'type' => 'news']);

    $query = http_build_query([
        'id' => [$first->getKey(), $third->getKey()],
        'is_active' => 1,
        'sort_by' => 'id',
        'sort_dir' => 'desc',
        'per_page' => 1,
        'page' => 1,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/pages?' . $query)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $third->getKey())
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 2);
});

it('rejects unsupported page list query params', function () {
    getJson(config('contento.routes.api.v1.prefix') . '/pages?unknown=1')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unknown']);
});

it('can show a page by id', function () {
    $page = Page::factory()->create();

    getJson(config('contento.routes.api.v1.prefix') . '/pages/' . $page->getKey())
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('title'), $page->title);
});

it('can show a page by slug', function () {
    $page = Page::factory()->create(['slug' => 'test-slug']);

    getJson(config('contento.routes.api.v1.prefix') . '/pages/test-slug')
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $page->getKey());
});

it('can create a page', function () {
    $data = [
        'title' => 'New Page',
        'content' => ['body' => 'Hello World'],
    ];

    postJson(config('contento.routes.api.v1.prefix') . '/pages', $data)
        ->assertCreated()
        ->assertJsonPath(contentoResourcePath('title'), 'New Page');

    $page = Page::query()->firstOrFail();

    assertDatabaseHas(config('contento.table_names.pages'), [
        'id' => $page->getKey(),
        'slug' => 'new-page',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'New Page',
    ]);
});

it('can create a page with multiple locale payload', function () {
    config()->set('translatable.locales', ['en', 'it', 'de']);
    app(Locales::class)->load();

    $data = [
        'author' => 'Test page',
        'en' => ['title' => 'My first post', 'abstract' => 'English abstract', 'content' => ['body' => 'English body']],
        'it' => ['title' => 'Il mio primo post', 'abstract' => 'Sommario breve', 'content' => ['body' => 'Contenuto italiano']],
        'de' => ['title' => 'Mein erster Beitrag', 'abstract' => 'Kurzer Übersicht'],
    ];

    postJson(config('contento.routes.api.v1.prefix') . '/pages', $data)
        ->assertCreated()
        ->assertJsonPath(contentoResourcePath('title'), 'My first post');

    $page = Page::query()->firstOrFail();

    assertDatabaseHas(config('contento.table_names.pages'), [
        'id' => $page->getKey(),
        'slug' => 'my-first-post',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'it',
        'attribute' => 'title',
        'value' => 'Il mio primo post',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'de',
        'attribute' => 'abstract',
        'value' => 'Kurzer Übersicht',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'it',
        'attribute' => 'content',
        'value' => json_encode(['body' => 'Contenuto italiano'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
});

it('returns translated page content for the requested locale', function () {
    config()->set('translatable.locales', ['en', 'it']);
    app(Locales::class)->load();

    postJson(config('contento.routes.api.v1.prefix') . '/pages', [
        'translations' => [
            'en' => ['title' => 'Home', 'content' => ['body' => 'English body']],
            'it' => ['title' => 'Casa', 'content' => ['body' => 'Corpo italiano']],
        ],
    ])->assertCreated();

    $page = Page::query()->firstOrFail();

    getJson(config('contento.routes.api.v1.prefix') . '/pages/' . $page->getKey(), ['Locale' => 'it'])
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('title'), 'Casa')
        ->assertJsonPath(contentoResourcePath('content.body'), 'Corpo italiano');
});

it('stores translated slugs and resolves pages by locale slug', function () {
    config()->set('translatable.locales', ['en', 'it']);
    app(Locales::class)->load();

    postJson(config('contento.routes.api.v1.prefix') . '/pages', [
        'en' => ['title' => 'Home Page'],
        'it' => ['title' => 'Pagina Casa'],
        'content' => ['body' => 'Localized body'],
    ])->assertCreated();

    $page = Page::query()->firstOrFail();

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'it',
        'attribute' => 'slug',
        'value' => 'pagina-casa',
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/pages/pagina-casa', ['Locale' => 'it'])
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $page->getKey())
        ->assertJsonPath(contentoResourcePath('slug'), 'pagina-casa');
});

it('keeps translated slugs in sync on update for provided locale titles', function () {
    config()->set('translatable.locales', ['en', 'it']);
    app(Locales::class)->load();

    postJson(config('contento.routes.api.v1.prefix') . '/pages', [
        'en' => ['title' => 'Home Page'],
        'it' => ['title' => 'Pagina Casa'],
        'content' => ['body' => 'Localized body'],
    ])->assertCreated();

    $page = Page::query()->firstOrFail();

    putJson(config('contento.routes.api.v1.prefix') . '/pages/' . $page->getKey(), [
        'en' => ['title' => 'Home Updated'],
        'it' => ['title' => 'Pagina Aggiornata'],
    ])->assertOk();

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'en',
        'attribute' => 'slug',
        'value' => 'home-updated',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'it',
        'attribute' => 'slug',
        'value' => 'pagina-aggiornata',
    ]);
});

it('stores page title and slug translations on create and update across locales', function () {
    config()->set('translatable.locales', ['en', 'it', 'de']);
    app(Locales::class)->load();

    postJson(config('contento.routes.api.v1.prefix') . '/pages', [
        'en' => ['title' => 'Landing Page'],
        'it' => ['title' => 'Pagina Atterraggio'],
        'content' => ['body' => 'Body content'],
    ])->assertCreated();

    $page = Page::query()->firstOrFail();

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'Landing Page',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'en',
        'attribute' => 'slug',
        'value' => 'landing-page',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'it',
        'attribute' => 'title',
        'value' => 'Pagina Atterraggio',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'it',
        'attribute' => 'slug',
        'value' => 'pagina-atterraggio',
    ]);

    putJson(config('contento.routes.api.v1.prefix') . '/pages/' . $page->getKey(), [
        'de' => ['title' => 'Startseite'],
    ])->assertOk();

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'de',
        'attribute' => 'title',
        'value' => 'Startseite',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'de',
        'attribute' => 'slug',
        'value' => 'startseite',
    ]);
});

it('generates page slug from translated titles when default locale title is missing', function () {
    config()->set('translatable.locales', ['en', 'it']);
    app(Locales::class)->load();

    postJson(config('contento.routes.api.v1.prefix') . '/pages', [
        'it' => ['title' => 'Pagina Locale'],
        'content' => ['body' => 'Ciao'],
    ])
        ->assertCreated();

    $page = Page::query()->firstOrFail();

    assertDatabaseHas(config('contento.table_names.pages'), [
        'id' => $page->getKey(),
        'slug' => 'pagina-locale',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'it',
        'attribute' => 'title',
        'value' => 'Pagina Locale',
    ]);
});

it('stores translations using the Locale header', function () {
    config()->set('translatable.locales', ['en', 'it']);
    app(Locales::class)->load();

    postJson(
        config('contento.routes.api.v1.prefix') . '/pages',
        [
            'title' => 'Titolo pagina',
            'content' => ['body' => 'Ciao'],
        ],
        ['Locale' => 'it']
    )
        ->assertCreated()
        ->assertJsonPath(contentoResourcePath('title'), 'Titolo pagina');

    $page = Page::query()->firstOrFail();

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'it',
        'attribute' => 'title',
        'value' => 'Titolo pagina',
    ]);
});

it('can update a page', function () {
    $page = Page::factory()->create();

    putJson(config('contento.routes.api.v1.prefix') . '/pages/' . $page->getKey(), [
        'title' => 'Updated Title',
    ])
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('title'), 'Updated Title');

    assertDatabaseHas(config('contento.table_names.pages'), [
        'id' => $page->getKey(),
        'slug' => 'updated-title',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'Updated Title',
    ]);
});

it('can delete a page', function () {
    $page = Page::factory()->create();

    deleteJson(config('contento.routes.api.v1.prefix') . '/pages/' . $page->getKey())
        ->assertNoContent();

    assertDatabaseMissing(config('contento.table_names.pages'), [
        'id' => $page->getKey(),
    ]);
});

it('slug creates correctly', function () {
    $data = [
        'title' => 'New Page',
        'content' => ['body' => 'Hello World'],
    ];

    postJson(config('contento.routes.api.v1.prefix') . '/pages', $data)
        ->assertCreated()
        ->assertJsonPath(contentoResourcePath('title'), 'New Page');

    $data = [
        'title' => 'New Page',
        'content' => ['body' => 'Hello World'],
    ];

    postJson(config('contento.routes.api.v1.prefix') . '/pages', $data)
        ->assertCreated()
        ->assertJsonPath(contentoResourcePath('title'), 'New Page');

    $page = Page::query()
        ->where('slug', 'new-page-1')
        ->firstOrFail();

    assertDatabaseHas(config('contento.table_names.pages'), [
        'id' => $page->getKey(),
        'slug' => 'new-page-1',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $page->getMorphClass(),
        'translatable_id' => $page->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'New Page',
    ]);
});
