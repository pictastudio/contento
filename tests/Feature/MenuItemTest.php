<?php

use PictaStudio\Contento\Models\{Menu, MenuItem};
use PictaStudio\Translatable\Locales;

use function Pest\Laravel\{assertDatabaseHas, getJson, postJson, putJson};

it('can list menu items', function () {
    MenuItem::factory()->count(3)->create();

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('can filter, sort and paginate menu items', function () {
    $menu = Menu::factory()->create();
    $first = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'active' => true,
    ]);
    MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'active' => false,
    ]);
    $third = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'active' => true,
    ]);

    $query = http_build_query([
        'menu_id' => $menu->getKey(),
        'id' => [$first->getKey(), $third->getKey()],
        'is_active' => 1,
        'sort_by' => 'id',
        'sort_dir' => 'desc',
        'per_page' => 1,
        'page' => 1,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items?' . $query)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $third->getKey())
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 2);
});

it('can filter menu items by title and link while including relations', function () {
    $menu = Menu::factory()->create();
    $matching = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'title' => 'Support',
        'link' => '/support',
    ]);
    MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'title' => 'Privacy',
        'link' => '/privacy',
    ]);

    getJson(
        config('contento.routes.api.v1.prefix')
        . '/menu-items?menu_id=' . $menu->getKey()
        . '&title=' . urlencode('Support')
        . '&link=' . urlencode('/support')
        . '&include=menu'
    )
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matching->getKey())
        ->assertJsonPath('data.0.menu.id', $menu->getKey());
});

it('rejects unsupported menu item list query params', function () {
    getJson(config('contento.routes.api.v1.prefix') . '/menu-items?unknown=1')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unknown']);
});

it('can create a menu item', function () {
    $menu = Menu::factory()->create();

    postJson(config('contento.routes.api.v1.prefix') . '/menu-items', [
        'menu_id' => $menu->getKey(),
        'title' => 'About us',
        'link' => '/about-us',
    ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'About us')
        ->assertJsonPath('data.link', '/about-us');

    $menuItem = MenuItem::query()->firstOrFail();

    assertDatabaseHas(config('contento.table_names.menu_items'), [
        'id' => $menuItem->getKey(),
        'menu_id' => $menu->getKey(),
        'slug' => 'about-us',
        'link' => '/about-us',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $menuItem->getMorphClass(),
        'translatable_id' => $menuItem->getKey(),
        'locale' => 'en',
        'attribute' => 'title',
        'value' => 'About us',
    ]);
});

it('can create a menu item with multiple locale payload', function () {
    config()->set('translatable.locales', ['en', 'it', 'de']);
    app(Locales::class)->load();

    $menu = Menu::factory()->create();

    postJson(config('contento.routes.api.v1.prefix') . '/menu-items', [
        'menu_id' => $menu->getKey(),
        'en' => ['title' => 'Support', 'link' => '/support'],
        'it' => ['title' => 'Supporto', 'link' => '/it/supporto'],
        'de' => ['title' => 'Hilfe', 'link' => '/de/hilfe'],
    ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Support');

    $menuItem = MenuItem::query()->firstOrFail();

    assertDatabaseHas('translations', [
        'translatable_type' => $menuItem->getMorphClass(),
        'translatable_id' => $menuItem->getKey(),
        'locale' => 'it',
        'attribute' => 'link',
        'value' => '/it/supporto',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $menuItem->getMorphClass(),
        'translatable_id' => $menuItem->getKey(),
        'locale' => 'de',
        'attribute' => 'slug',
        'value' => 'hilfe',
    ]);
});

it('can return menu items as tree', function () {
    $menu = Menu::factory()->create();
    $rootA = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'title' => 'Root A',
    ]);
    $rootB = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'title' => 'Root B',
    ]);

    MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'parent_id' => $rootA->getKey(),
        'title' => 'Root A Child',
    ]);
    MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'parent_id' => $rootB->getKey(),
        'title' => 'Root B Child',
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items?menu_id=' . $menu->getKey() . '&as_tree=1')
        ->assertOk()
        ->assertJsonPath('data.0.id', $rootA->getKey())
        ->assertJsonPath('data.0.children.0.title', 'Root A Child')
        ->assertJsonPath('data.1.id', $rootB->getKey())
        ->assertJsonPath('data.1.children.0.title', 'Root B Child');
});

it('can include parent children and menu relations', function () {
    $menu = Menu::factory()->create(['title' => 'Main']);
    $parent = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'title' => 'Parent',
    ]);
    $child = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'parent_id' => $parent->getKey(),
        'title' => 'Child',
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items/' . $parent->getKey() . '?include=menu,children')
        ->assertOk()
        ->assertJsonPath('data.menu.id', $menu->getKey())
        ->assertJsonPath('data.children.0.id', $child->getKey());

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items/' . $child->getKey() . '?include=parent')
        ->assertOk()
        ->assertJsonPath('data.parent.id', $parent->getKey());
});

it('prevents invalid parent assignments across menus and descendants', function () {
    $firstMenu = Menu::factory()->create();
    $secondMenu = Menu::factory()->create();

    $parent = MenuItem::factory()->create([
        'menu_id' => $firstMenu->getKey(),
    ]);
    $child = MenuItem::factory()->create([
        'menu_id' => $firstMenu->getKey(),
        'parent_id' => $parent->getKey(),
    ]);
    $otherMenuParent = MenuItem::factory()->create([
        'menu_id' => $secondMenu->getKey(),
    ]);

    putJson(config('contento.routes.api.v1.prefix') . '/menu-items/' . $parent->getKey(), [
        'parent_id' => $child->getKey(),
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['parent_id']);

    putJson(config('contento.routes.api.v1.prefix') . '/menu-items/' . $parent->getKey(), [
        'parent_id' => $otherMenuParent->getKey(),
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['parent_id']);
});

it('moves descendants to the new menu when a parent item changes menu', function () {
    $firstMenu = Menu::factory()->create();
    $secondMenu = Menu::factory()->create();

    $parent = MenuItem::factory()->create([
        'menu_id' => $firstMenu->getKey(),
        'title' => 'Parent',
    ]);
    $child = MenuItem::factory()->create([
        'menu_id' => $firstMenu->getKey(),
        'parent_id' => $parent->getKey(),
        'title' => 'Child',
    ]);

    putJson(config('contento.routes.api.v1.prefix') . '/menu-items/' . $parent->getKey(), [
        'menu_id' => $secondMenu->getKey(),
        'parent_id' => null,
    ])
        ->assertOk()
        ->assertJsonPath('data.menu_id', $secondMenu->getKey());

    assertDatabaseHas(config('contento.table_names.menu_items'), [
        'id' => $child->getKey(),
        'menu_id' => $secondMenu->getKey(),
    ]);
});
