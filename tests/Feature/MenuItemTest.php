<?php

use Illuminate\Support\Facades\DB;
use PictaStudio\Contento\Models\{Menu, MenuItem};
use PictaStudio\Translatable\Locales;

use function Pest\Laravel\{assertDatabaseHas, deleteJson, getJson, postJson, putJson};

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
        'sort_order' => 20,
    ]);
    MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'active' => false,
        'sort_order' => 15,
    ]);
    $third = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'active' => true,
        'sort_order' => 5,
    ]);

    $query = http_build_query([
        'menu_id' => $menu->getKey(),
        'id' => [$first->getKey(), $third->getKey()],
        'is_active' => 1,
        'sort_by' => 'sort_order',
        'sort_dir' => 'asc',
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

it('orders menu items by sort order by default', function () {
    $menu = Menu::factory()->create();
    $lowest = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'sort_order' => 5,
    ]);
    $middle = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'sort_order' => 10,
    ]);
    $highest = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'sort_order' => 20,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items?menu_id=' . $menu->getKey())
        ->assertOk()
        ->assertJsonPath('data.0.id', $lowest->getKey())
        ->assertJsonPath('data.1.id', $middle->getKey())
        ->assertJsonPath('data.2.id', $highest->getKey());
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
        . '&title=' . urlencode('PORT')
        . '&link=' . urlencode('/SUP')
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
        'sort_order' => 5,
    ])
        ->assertCreated()
        ->assertJsonPath(contentoResourcePath('title'), 'About us')
        ->assertJsonPath(contentoResourcePath('link'), '/about-us')
        ->assertJsonPath(contentoResourcePath('sort_order'), 5);

    $menuItem = MenuItem::query()->firstOrFail();

    assertDatabaseHas(config('contento.table_names.menu_items'), [
        'id' => $menuItem->getKey(),
        'menu_id' => $menu->getKey(),
        'slug' => 'about-us',
        'link' => '/about-us',
        'sort_order' => 5,
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
        ->assertJsonPath(contentoResourcePath('title'), 'Support');

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

it('bulk upserts menu items by updating existing records and creating new ones', function () {
    config()->set('translatable.locales', ['en', 'it']);
    app(Locales::class)->load();

    $menu = Menu::factory()->create();
    $existingMenuItem = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'title' => 'Old support',
        'link' => '/support',
        'active' => true,
        'sort_order' => 9,
    ]);

    postJson(config('contento.routes.api.v1.prefix') . '/menu-items/bulk/upsert', [
        'menu_items' => [
            [
                'id' => $existingMenuItem->getKey(),
                'title' => 'Updated support',
                'active' => false,
                'sort_order' => 3,
            ],
            [
                'menu_id' => $menu->getKey(),
                'sort_order' => 7,
                'en' => [
                    'title' => 'Contact',
                    'link' => '/contact',
                ],
                'it' => [
                    'title' => 'Contatti',
                    'link' => '/it/contatti',
                ],
            ],
        ],
    ])
        ->assertOk()
        ->assertJsonCount(2, contentoCollectionPath())
        ->assertJsonPath(contentoCollectionPath('0.id'), $existingMenuItem->getKey())
        ->assertJsonPath(contentoCollectionPath('0.title'), 'Updated support')
        ->assertJsonPath(contentoCollectionPath('0.active'), false)
        ->assertJsonPath(contentoCollectionPath('0.sort_order'), 3)
        ->assertJsonPath(contentoCollectionPath('1.sort_order'), 7)
        ->assertJsonPath(contentoCollectionPath('1.title'), 'Contact');

    $createdMenuItem = MenuItem::query()
        ->where('title', 'Contact')
        ->firstOrFail();

    assertDatabaseHas(config('contento.table_names.menu_items'), [
        'id' => $existingMenuItem->getKey(),
        'title' => 'Updated support',
        'active' => false,
        'sort_order' => 3,
    ]);

    assertDatabaseHas(config('contento.table_names.menu_items'), [
        'id' => $createdMenuItem->getKey(),
        'menu_id' => $menu->getKey(),
        'slug' => 'contact',
        'link' => '/contact',
        'sort_order' => 7,
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $createdMenuItem->getMorphClass(),
        'translatable_id' => $createdMenuItem->getKey(),
        'locale' => 'it',
        'attribute' => 'title',
        'value' => 'Contatti',
    ]);

    assertDatabaseHas('translations', [
        'translatable_type' => $createdMenuItem->getMorphClass(),
        'translatable_id' => $createdMenuItem->getKey(),
        'locale' => 'it',
        'attribute' => 'slug',
        'value' => 'contatti',
    ]);
});

it('bulk upserts menu items from a raw array payload', function () {
    $menu = Menu::factory()->create();

    postJson(config('contento.routes.api.v1.prefix') . '/menu-items/bulk/upsert', [
        [
            'menu_id' => $menu->getKey(),
            'title' => 'Raw payload menu item',
            'link' => '/raw-payload-menu-item',
        ],
    ])
        ->assertOk()
        ->assertJsonCount(1, contentoCollectionPath())
        ->assertJsonPath(contentoCollectionPath('0.title'), 'Raw payload menu item');

    assertDatabaseHas(config('contento.table_names.menu_items'), [
        'title' => 'Raw payload menu item',
        'slug' => 'raw-payload-menu-item',
    ]);
});

it('can return menu items as tree', function () {
    $menu = Menu::factory()->create();
    $rootA = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'title' => 'Root A',
        'sort_order' => 20,
    ]);
    $rootB = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'title' => 'Root B',
        'sort_order' => 10,
    ]);

    MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'parent_id' => $rootA->getKey(),
        'title' => 'Root A Child',
        'sort_order' => 5,
    ]);
    MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'parent_id' => $rootB->getKey(),
        'title' => 'Root B Child',
        'sort_order' => 5,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items?menu_id=' . $menu->getKey() . '&as_tree=1')
        ->assertOk()
        ->assertJsonPath(contentoCollectionPath('0.id'), $rootB->getKey())
        ->assertJsonPath(contentoCollectionPath('0.children.0.title'), 'Root B Child')
        ->assertJsonPath(contentoCollectionPath('1.id'), $rootA->getKey())
        ->assertJsonPath(contentoCollectionPath('1.children.0.title'), 'Root A Child');

    expect((string) $rootA->fresh()->path)->toBe((string) $rootA->getKey());
});

it('returns nested descendants in the menu item tree', function () {
    $menu = Menu::factory()->create();
    $root = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'title' => 'Root',
    ]);
    $child = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'parent_id' => $root->getKey(),
        'title' => 'Child',
    ]);
    $grandChild = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'parent_id' => $child->getKey(),
        'title' => 'Grandchild',
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items?menu_id=' . $menu->getKey() . '&as_tree=1')
        ->assertOk()
        ->assertJsonPath(contentoCollectionPath('0.id'), $root->getKey())
        ->assertJsonPath(contentoCollectionPath('0.children.0.id'), $child->getKey())
        ->assertJsonPath(contentoCollectionPath('0.children.0.children.0.id'), $grandChild->getKey())
        ->assertJsonPath(contentoCollectionPath('0.children.0.children.0.parent_id'), $child->getKey());

    expect((string) $child->fresh()->path)->toBe($root->getKey() . '.' . $child->getKey())
        ->and((string) $grandChild->fresh()->path)->toBe(
            $root->getKey() . '.' . $child->getKey() . '.' . $grandChild->getKey()
        );
});

it('rebuilds child menu item paths when deleting a parent item', function () {
    $menu = Menu::factory()->create();
    $root = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'title' => 'Root',
        'sort_order' => 1,
    ]);
    $child = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'parent_id' => $root->getKey(),
        'title' => 'Child',
        'sort_order' => 2,
    ]);
    $grandChild = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'parent_id' => $child->getKey(),
        'title' => 'Grandchild',
        'sort_order' => 3,
    ]);

    deleteJson(config('contento.routes.api.v1.prefix') . '/menu-items/' . $root->getKey())
        ->assertNoContent();

    $child->refresh();
    $grandChild->refresh();

    expect($child->parent_id)->toBeNull()
        ->and((string) $child->path)->toBe((string) $child->getKey())
        ->and((string) $grandChild->path)->toBe($child->getKey() . '.' . $grandChild->getKey());

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items?menu_id=' . $menu->getKey() . '&as_tree=1')
        ->assertOk()
        ->assertJsonPath(contentoCollectionPath('0.id'), $child->getKey())
        ->assertJsonPath(contentoCollectionPath('0.children.0.id'), $grandChild->getKey());
});

it('serializes casted tree paths as strings for menu items', function () {
    $menu = Menu::factory()->create();
    $menuItem = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items/' . $menuItem->getKey())
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('path'), (string) $menuItem->fresh()->path);
});

it('can include parent children and menu relations', function () {
    $menu = Menu::factory()->create(['title' => 'Main']);
    $parent = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'title' => 'Parent',
    ]);
    $slowChild = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'parent_id' => $parent->getKey(),
        'title' => 'Child slow',
        'sort_order' => 20,
    ]);
    $fastChild = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'parent_id' => $parent->getKey(),
        'title' => 'Child fast',
        'sort_order' => 5,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items/' . $parent->getKey() . '?include=menu,children')
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('menu.id'), $menu->getKey())
        ->assertJsonPath(contentoResourcePath('children.0.id'), $fastChild->getKey())
        ->assertJsonPath(contentoResourcePath('children.1.id'), $slowChild->getKey());

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items/' . $fastChild->getKey() . '?include=parent')
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('parent.id'), $parent->getKey());
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
        ->assertJsonPath(contentoResourcePath('menu_id'), $secondMenu->getKey());

    assertDatabaseHas(config('contento.table_names.menu_items'), [
        'id' => $child->getKey(),
        'menu_id' => $secondMenu->getKey(),
    ]);
});

it('rebuilds descendant paths when a menu item is reparented', function () {
    if (DB::getDriverName() === 'sqlite') {
        test()->markTestSkipped('Tree path rebuilding uses MySQL functions provided by nevadskiy/laravel-tree.');
    }

    $menu = Menu::factory()->create();

    $rootA = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'title' => 'Root A',
    ]);
    $rootB = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'title' => 'Root B',
    ]);
    $child = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'parent_id' => $rootA->getKey(),
        'title' => 'Child',
    ]);
    $grandChild = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'parent_id' => $child->getKey(),
        'title' => 'Grandchild',
    ]);

    putJson(config('contento.routes.api.v1.prefix') . '/menu-items/' . $child->getKey(), [
        'parent_id' => $rootB->getKey(),
    ])
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('parent_id'), $rootB->getKey())
        ->assertJsonPath(contentoResourcePath('path'), $rootB->getKey() . '.' . $child->getKey());

    expect((string) $child->fresh()->path)->toBe($rootB->getKey() . '.' . $child->getKey())
        ->and((string) $grandChild->fresh()->path)->toBe(
            $rootB->getKey() . '.' . $child->getKey() . '.' . $grandChild->getKey()
        );
})->group('tree');

it('stores parent_id as null when no parent is assigned', function () {
    $menu = Menu::factory()->create();

    postJson(config('contento.routes.api.v1.prefix') . '/menu-items', [
        'menu_id' => $menu->getKey(),
        'title' => 'Root item',
        'link' => '/root-item',
        'parent_id' => null,
    ])
        ->assertCreated()
        ->assertJsonPath(contentoResourcePath('parent_id'), null);

    assertDatabaseHas(config('contento.table_names.menu_items'), [
        'menu_id' => $menu->getKey(),
        'title' => 'Root item',
        'parent_id' => null,
    ]);
});

it('validates duplicate ids and missing update targets in menu item bulk upserts', function () {
    $menu = Menu::factory()->create();
    $existingMenuItem = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
    ]);
    $missingMenuItemId = $existingMenuItem->getKey() + 999;

    postJson(config('contento.routes.api.v1.prefix') . '/menu-items/bulk/upsert', [
        'menu_items' => [
            [
                'id' => $existingMenuItem->getKey(),
                'title' => 'First update',
            ],
            [
                'id' => $existingMenuItem->getKey(),
                'title' => 'Duplicate update',
            ],
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['menu_items.1.id']);

    postJson(config('contento.routes.api.v1.prefix') . '/menu-items/bulk/upsert', [
        'menu_items' => [
            [
                'id' => $missingMenuItemId,
                'title' => 'Missing update target',
            ],
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['menu_items']);

    postJson(config('contento.routes.api.v1.prefix') . '/menu-items/bulk/upsert', [
        'menu_items' => [
            [
                'link' => '/missing-required-fields',
            ],
        ],
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'menu_items.0.menu_id',
            'menu_items.0.title',
        ]);
});
