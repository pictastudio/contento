<?php

use Illuminate\Auth\GenericUser;
use PictaStudio\Contento\Models\{Menu, MenuItem};
use PictaStudio\Translatable\Locales;

use function Pest\Laravel\{actingAs, assertDatabaseHas, getJson, postJson, putJson};

it('can list menus', function () {
    Menu::factory()->count(3)->create();

    getJson(config('contento.routes.api.v1.prefix') . '/menus')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('can filter, sort and paginate menus', function () {
    $first = Menu::factory()->create(['active' => true]);
    Menu::factory()->create(['active' => false]);
    $third = Menu::factory()->create(['active' => true]);

    $query = http_build_query([
        'id' => [$first->getKey(), $third->getKey()],
        'is_active' => 1,
        'sort_by' => 'id',
        'sort_dir' => 'desc',
        'per_page' => 1,
        'page' => 1,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/menus?' . $query)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $third->getKey())
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 2);
});

it('can list all menus with the all filter', function () {
    $visible = Menu::factory()->create([
        'active' => true,
        'visible_date_from' => now()->subDay(),
        'visible_date_to' => now()->addDay(),
    ]);
    $inactive = Menu::factory()->create([
        'active' => false,
        'visible_date_from' => now()->subDay(),
        'visible_date_to' => now()->addDay(),
    ]);
    $future = Menu::factory()->create([
        'active' => true,
        'visible_date_from' => now()->addDay(),
        'visible_date_to' => null,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/menus?all=1&per_page=1&sort_by=id&sort_dir=asc')
        ->assertOk()
        ->assertJsonCount(3, contentoCollectionPath())
        ->assertJsonPath(contentoCollectionPath('0.id'), $visible->getKey())
        ->assertJsonPath(contentoCollectionPath('1.id'), $inactive->getKey())
        ->assertJsonPath(contentoCollectionPath('2.id'), $future->getKey())
        ->assertJsonMissingPath('meta')
        ->assertJsonMissingPath('links');

    getJson(config('contento.routes.api.v1.prefix') . '/menus?filter=all&per_page=1&sort_by=id&sort_dir=asc')
        ->assertOk()
        ->assertJsonCount(3, contentoCollectionPath())
        ->assertJsonPath(contentoCollectionPath('0.id'), $visible->getKey())
        ->assertJsonPath(contentoCollectionPath('1.id'), $inactive->getKey())
        ->assertJsonPath(contentoCollectionPath('2.id'), $future->getKey())
        ->assertJsonMissingPath('meta')
        ->assertJsonMissingPath('links');
});

it('can filter menus by title and include items on index', function () {
    $menu = Menu::factory()->create([
        'title' => 'Main Navigation',
    ]);
    Menu::factory()->create([
        'title' => 'Footer Navigation',
    ]);
    $item = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'title' => 'Home',
    ]);

    getJson(
        config('contento.routes.api.v1.prefix')
        . '/menus?title=' . urlencode('mAiN')
        . '&include=items'
    )
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $menu->getKey())
        ->assertJsonPath('data.0.items.0.id', $item->getKey());
});

it('rejects unsupported menu list query params', function () {
    getJson(config('contento.routes.api.v1.prefix') . '/menus?unknown=1')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unknown']);
});

it('can create a menu', function () {
    postJson(config('contento.routes.api.v1.prefix') . '/menus', [
        'title' => 'Main Navigation',
    ])
        ->assertCreated()
        ->assertJsonPath(contentoResourcePath('title'), 'Main Navigation');

    $menu = Menu::query()->firstOrFail();

    assertDatabaseHas(config('contento.table_names.menus'), [
        'id' => $menu->getKey(),
        'slug' => 'main-navigation',
        'created_by' => null,
        'updated_by' => null,
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $menu->getMorphClass(),
        'translatable_id' => $menu->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'Main Navigation',
    ]);
});

it('stores author ids when tracking is enabled and a user is authenticated', function () {
    config()->set('contento.authors.track', true);

    actingAs(new GenericUser(['id' => 42]));

    postJson(config('contento.routes.api.v1.prefix') . '/menus', [
        'title' => 'Authenticated Navigation',
    ])->assertCreated();

    $menu = Menu::query()->firstOrFail();

    assertDatabaseHas(config('contento.table_names.menus'), [
        'id' => $menu->getKey(),
        'created_by' => 42,
        'updated_by' => null,
    ]);

    putJson(config('contento.routes.api.v1.prefix') . '/menus/' . $menu->getKey(), [
        'title' => 'Authenticated Navigation Updated',
    ])->assertOk();

    assertDatabaseHas(config('contento.table_names.menus'), [
        'id' => $menu->getKey(),
        'created_by' => 42,
        'updated_by' => 42,
    ]);
});

it('can create a menu with multiple locale payload', function () {
    config()->set('translatable.locales', ['en', 'it', 'de']);
    app(Locales::class)->load();

    postJson(config('contento.routes.api.v1.prefix') . '/menus', [
        'en' => ['title' => 'Main Navigation'],
        'it' => ['title' => 'Navigazione Principale'],
        'de' => ['title' => 'Hauptnavigation'],
    ])
        ->assertCreated()
        ->assertJsonPath(contentoResourcePath('title'), 'Main Navigation');

    $menu = Menu::query()->firstOrFail();

    assertDatabaseHas('translations', [
        'translatable_type' => $menu->getMorphClass(),
        'translatable_id' => $menu->getKey(),
        'locale' => 'it',
        'attribute' => 'title',
        'value' => 'Navigazione Principale',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $menu->getMorphClass(),
        'translatable_id' => $menu->getKey(),
        'locale' => 'de',
        'attribute' => 'slug',
        'value' => 'hauptnavigation',
    ]);
});

it('can show menu items when requested', function () {
    $menu = Menu::factory()->create(['title' => 'Footer']);
    $firstItem = $menu->items()->create([
        'title' => 'Contacts',
        'link' => '/contacts',
    ]);
    $secondItem = $menu->items()->create([
        'title' => 'Privacy',
        'link' => '/privacy',
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/menus/' . $menu->getKey() . '?include=items')
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $menu->getKey())
        ->assertJsonPath(contentoResourcePath('items.0.id'), $firstItem->getKey())
        ->assertJsonPath(contentoResourcePath('items.1.id'), $secondItem->getKey());
});

it('can update a menu', function () {
    $menu = Menu::factory()->create([
        'title' => 'Old Navigation',
    ]);

    putJson(config('contento.routes.api.v1.prefix') . '/menus/' . $menu->getKey(), [
        'title' => 'Updated Navigation',
        'active' => false,
    ])
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('title'), 'Updated Navigation')
        ->assertJsonPath(contentoResourcePath('active'), false);

    assertDatabaseHas(config('contento.table_names.menus'), [
        'id' => $menu->getKey(),
        'slug' => 'updated-navigation',
        'active' => false,
        'updated_by' => null,
    ]);
});
