<?php

use PictaStudio\Contento\Models\{ContentTag, Faq, FaqCategory, Menu, MenuItem, Modal, Page};

use function Pest\Laravel\getJson;

it('applies active and publication-related scopes to pages by default and allows excluding them', function () {
    $visiblePublished = Page::factory()->create([
        'active' => true,
        'visible_date_from' => now()->subDay(),
        'visible_date_to' => now()->addDay(),
        'published_at' => now()->subMinute(),
    ]);

    $inactive = Page::factory()->create([
        'active' => false,
        'visible_date_from' => now()->subDay(),
        'visible_date_to' => now()->addDay(),
        'published_at' => now()->subMinute(),
    ]);

    $futureVisible = Page::factory()->create([
        'active' => true,
        'visible_date_from' => now()->addDay(),
        'visible_date_to' => null,
        'published_at' => now()->subMinute(),
    ]);

    $futurePublished = Page::factory()->create([
        'active' => true,
        'visible_date_from' => now()->subDay(),
        'visible_date_to' => now()->addDay(),
        'published_at' => now()->addDay(),
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/pages')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $visiblePublished->getKey());

    getJson(config('contento.routes.api.v1.prefix') . '/pages?exclude_all_scopes=1')
        ->assertOk()
        ->assertJsonCount(4, 'data');

    getJson(config('contento.routes.api.v1.prefix') . '/pages?exclude_active_scope=1')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    getJson(config('contento.routes.api.v1.prefix') . '/pages?exclude_date_range_scope=1')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    getJson(config('contento.routes.api.v1.prefix') . '/pages?exclude_published_scope=1')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    getJson(config('contento.routes.api.v1.prefix') . '/pages?active=0')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $inactive->getKey());

    getJson(config('contento.routes.api.v1.prefix') . '/pages?visible_date_from_start=' . urlencode($futureVisible->visible_date_from?->toDateTimeString() ?? ''))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $futureVisible->getKey());

    getJson(config('contento.routes.api.v1.prefix') . '/pages?published_at_start=' . urlencode($futurePublished->published_at?->toDateTimeString() ?? ''))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $futurePublished->getKey());
});

it('applies active scopes to faq categories by default and allows explicit filters to override them', function () {
    $active = FaqCategory::factory()->create(['active' => true]);
    $inactive = FaqCategory::factory()->create(['active' => false]);

    getJson(config('contento.routes.api.v1.prefix') . '/faq-categories')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $active->getKey());

    getJson(config('contento.routes.api.v1.prefix') . '/faq-categories?active=0')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $inactive->getKey());
});

it('applies active and date range scopes to menus and menu items by default and allows overrides', function () {
    $visibleMenu = Menu::factory()->create([
        'active' => true,
        'visible_date_from' => now()->subDay(),
        'visible_date_to' => now()->addDay(),
    ]);
    Menu::factory()->create([
        'active' => false,
        'visible_date_from' => now()->subDay(),
        'visible_date_to' => now()->addDay(),
    ]);
    $futureMenu = Menu::factory()->create([
        'active' => true,
        'visible_date_from' => now()->addDay(),
        'visible_date_to' => null,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/menus')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $visibleMenu->getKey());

    getJson(config('contento.routes.api.v1.prefix') . '/menus?exclude_all_scopes=1')
        ->assertOk()
        ->assertJsonCount(3, 'data');

    getJson(config('contento.routes.api.v1.prefix') . '/menus?exclude_active_scope=1')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    getJson(config('contento.routes.api.v1.prefix') . '/menus?exclude_date_range_scope=1')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    getJson(config('contento.routes.api.v1.prefix') . '/menus?visible_date_from_start=' . urlencode($futureMenu->visible_date_from?->toDateTimeString() ?? ''))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $futureMenu->getKey());

    $menu = Menu::factory()->create();
    $visibleItem = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'active' => true,
        'visible_date_from' => now()->subDay(),
        'visible_date_to' => now()->addDay(),
    ]);
    MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'active' => false,
        'visible_date_from' => now()->subDay(),
        'visible_date_to' => now()->addDay(),
    ]);
    $futureItem = MenuItem::factory()->create([
        'menu_id' => $menu->getKey(),
        'active' => true,
        'visible_date_from' => now()->addDay(),
        'visible_date_to' => null,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items?menu_id=' . $menu->getKey())
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $visibleItem->getKey());

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items?menu_id=' . $menu->getKey() . '&exclude_all_scopes=1')
        ->assertOk()
        ->assertJsonCount(3, 'data');

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items?menu_id=' . $menu->getKey() . '&exclude_active_scope=1')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items?menu_id=' . $menu->getKey() . '&exclude_date_range_scope=1')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items?menu_id=' . $menu->getKey() . '&visible_date_from_start=' . urlencode($futureItem->visible_date_from?->toDateTimeString() ?? ''))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $futureItem->getKey());
});

it('applies active and date range scopes to content tags faqs and modals by default and allows overrides', function () {
    $visibleTag = ContentTag::factory()->create([
        'active' => true,
        'visible_from' => now()->subDay(),
        'visible_until' => now()->addDay(),
    ]);
    $inactiveTag = ContentTag::factory()->create([
        'active' => false,
        'visible_from' => now()->subDay(),
        'visible_until' => now()->addDay(),
    ]);
    $futureTag = ContentTag::factory()->create([
        'active' => true,
        'visible_from' => now()->addDay(),
        'visible_until' => null,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/content-tags')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $visibleTag->getKey());

    getJson(config('contento.routes.api.v1.prefix') . '/content-tags?exclude_all_scopes=1')
        ->assertOk()
        ->assertJsonCount(3, 'data');

    getJson(config('contento.routes.api.v1.prefix') . '/content-tags?active=0')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $inactiveTag->getKey());

    getJson(config('contento.routes.api.v1.prefix') . '/content-tags?visible_from_start=' . urlencode($futureTag->visible_from?->toDateTimeString() ?? ''))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $futureTag->getKey());

    $category = FaqCategory::factory()->create(['active' => true]);

    $visibleFaq = Faq::factory()->create([
        'faq_category_id' => $category->getKey(),
        'active' => true,
        'visible_date_from' => now()->subDay(),
        'visible_date_to' => now()->addDay(),
    ]);
    $futureFaq = Faq::factory()->create([
        'faq_category_id' => $category->getKey(),
        'active' => true,
        'visible_date_from' => now()->addDay(),
        'visible_date_to' => null,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/faqs')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $visibleFaq->getKey());

    getJson(config('contento.routes.api.v1.prefix') . '/faqs?visible_date_from_start=' . urlencode($futureFaq->visible_date_from?->toDateTimeString() ?? ''))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $futureFaq->getKey());

    $visibleModal = Modal::factory()->create([
        'active' => true,
        'visible_date_from' => now()->subDay(),
        'visible_date_to' => now()->addDay(),
    ]);
    $futureModal = Modal::factory()->create([
        'active' => true,
        'visible_date_from' => now()->addDay(),
        'visible_date_to' => null,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/modals')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $visibleModal->getKey());

    getJson(config('contento.routes.api.v1.prefix') . '/modals?visible_date_from_start=' . urlencode($futureModal->visible_date_from?->toDateTimeString() ?? ''))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $futureModal->getKey());
});
