<?php

use PictaStudio\Contento\Models\{FaqCategory, Menu};

use function Pest\Laravel\{assertDatabaseMissing, deleteJson, getJson, postJson, putJson};

it('resolves slug-enabled resources by slug for show, update, and destroy', function () {
    $pageResponse = postJson(config('contento.routes.api.v1.prefix') . '/pages', [
        'title' => 'Landing Page',
        'content' => ['body' => 'Page body'],
    ])->assertCreated();

    $pageSlug = $pageResponse->json(contentoResourcePath('slug'));
    $pageId = $pageResponse->json(contentoResourcePath('id'));

    getJson(config('contento.routes.api.v1.prefix') . '/pages/' . $pageSlug)
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $pageId);

    $pageUpdateResponse = putJson(config('contento.routes.api.v1.prefix') . '/pages/' . $pageSlug, [
        'title' => 'Landing Page Updated',
    ])->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $pageId);

    deleteJson(config('contento.routes.api.v1.prefix') . '/pages/' . $pageUpdateResponse->json(contentoResourcePath('slug')))->assertNoContent();
    assertDatabaseMissing(config('contento.table_names.pages'), ['id' => $pageId]);

    $menuResponse = postJson(config('contento.routes.api.v1.prefix') . '/menus', [
        'title' => 'Main Menu',
    ])->assertCreated();

    $menuSlug = $menuResponse->json(contentoResourcePath('slug'));
    $menuId = $menuResponse->json(contentoResourcePath('id'));

    getJson(config('contento.routes.api.v1.prefix') . '/menus/' . $menuSlug)
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $menuId);

    $menuUpdateResponse = putJson(config('contento.routes.api.v1.prefix') . '/menus/' . $menuSlug, [
        'title' => 'Main Menu Updated',
    ])->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $menuId);

    deleteJson(config('contento.routes.api.v1.prefix') . '/menus/' . $menuUpdateResponse->json(contentoResourcePath('slug')))->assertNoContent();
    assertDatabaseMissing(config('contento.table_names.menus'), ['id' => $menuId]);

    $menuForItems = Menu::factory()->create();

    $menuItemResponse = postJson(config('contento.routes.api.v1.prefix') . '/menu-items', [
        'menu_id' => $menuForItems->getKey(),
        'title' => 'Support',
        'link' => '/support',
    ])->assertCreated();

    $menuItemSlug = $menuItemResponse->json(contentoResourcePath('slug'));
    $menuItemId = $menuItemResponse->json(contentoResourcePath('id'));

    getJson(config('contento.routes.api.v1.prefix') . '/menu-items/' . $menuItemSlug)
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $menuItemId);

    $menuItemUpdateResponse = putJson(config('contento.routes.api.v1.prefix') . '/menu-items/' . $menuItemSlug, [
        'title' => 'Support Updated',
    ])->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $menuItemId);

    deleteJson(config('contento.routes.api.v1.prefix') . '/menu-items/' . $menuItemUpdateResponse->json(contentoResourcePath('slug')))->assertNoContent();
    assertDatabaseMissing(config('contento.table_names.menu_items'), ['id' => $menuItemId]);

    $categoryResponse = postJson(config('contento.routes.api.v1.prefix') . '/faq-categories', [
        'title' => 'General Category',
    ])->assertCreated();

    $categorySlug = $categoryResponse->json(contentoResourcePath('slug'));
    $categoryId = $categoryResponse->json(contentoResourcePath('id'));

    getJson(config('contento.routes.api.v1.prefix') . '/faq-categories/' . $categorySlug)
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $categoryId);

    $categoryUpdateResponse = putJson(config('contento.routes.api.v1.prefix') . '/faq-categories/' . $categorySlug, [
        'title' => 'General Category Updated',
    ])->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $categoryId);

    deleteJson(config('contento.routes.api.v1.prefix') . '/faq-categories/' . $categoryUpdateResponse->json(contentoResourcePath('slug')))->assertNoContent();
    assertDatabaseMissing(config('contento.table_names.faq_categories'), ['id' => $categoryId]);

    $faqCategory = FaqCategory::factory()->create();

    $faqResponse = postJson(config('contento.routes.api.v1.prefix') . '/faqs', [
        'faq_category_id' => $faqCategory->getKey(),
        'title' => 'How it works',
        'content' => 'Faq answer',
    ])->assertCreated();

    $faqSlug = $faqResponse->json(contentoResourcePath('slug'));
    $faqId = $faqResponse->json(contentoResourcePath('id'));

    getJson(config('contento.routes.api.v1.prefix') . '/faqs/' . $faqSlug)
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $faqId);

    $faqUpdateResponse = putJson(config('contento.routes.api.v1.prefix') . '/faqs/' . $faqSlug, [
        'title' => 'How it works updated',
    ])->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $faqId);

    deleteJson(config('contento.routes.api.v1.prefix') . '/faqs/' . $faqUpdateResponse->json(contentoResourcePath('slug')))->assertNoContent();
    assertDatabaseMissing(config('contento.table_names.faqs'), ['id' => $faqId]);

    $mailFormResponse = postJson(config('contento.routes.api.v1.prefix') . '/mail-forms', [
        'name' => 'Support Request',
        'email_to' => 'support@example.com',
    ])->assertCreated();

    $mailFormSlug = $mailFormResponse->json(contentoResourcePath('slug'));
    $mailFormId = $mailFormResponse->json(contentoResourcePath('id'));

    getJson(config('contento.routes.api.v1.prefix') . '/mail-forms/' . $mailFormSlug)
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $mailFormId);

    $mailFormUpdateResponse = putJson(config('contento.routes.api.v1.prefix') . '/mail-forms/' . $mailFormSlug, [
        'name' => 'Support Team',
    ])->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $mailFormId);

    deleteJson(config('contento.routes.api.v1.prefix') . '/mail-forms/' . $mailFormUpdateResponse->json(contentoResourcePath('slug')))->assertNoContent();
    assertDatabaseMissing(config('contento.table_names.mail_forms'), ['id' => $mailFormId]);

    $modalResponse = postJson(config('contento.routes.api.v1.prefix') . '/modals', [
        'title' => 'Newsletter Popup',
        'content' => 'Join our newsletter',
    ])->assertCreated();

    $modalSlug = $modalResponse->json(contentoResourcePath('slug'));
    $modalId = $modalResponse->json(contentoResourcePath('id'));

    getJson(config('contento.routes.api.v1.prefix') . '/modals/' . $modalSlug)
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $modalId);

    $modalUpdateResponse = putJson(config('contento.routes.api.v1.prefix') . '/modals/' . $modalSlug, [
        'title' => 'Newsletter Popup Updated',
    ])->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $modalId);

    deleteJson(config('contento.routes.api.v1.prefix') . '/modals/' . $modalUpdateResponse->json(contentoResourcePath('slug')))->assertNoContent();
    assertDatabaseMissing(config('contento.table_names.modals'), ['id' => $modalId]);

    $contentTagResponse = postJson(config('contento.routes.api.v1.prefix') . '/content-tags', [
        'name' => 'Homepage',
        'sort_order' => 1,
    ])->assertCreated();

    $contentTagSlug = $contentTagResponse->json(contentoResourcePath('slug'));
    $contentTagId = $contentTagResponse->json(contentoResourcePath('id'));

    getJson(config('contento.routes.api.v1.prefix') . '/content-tags/' . $contentTagSlug)
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $contentTagId);

    $contentTagUpdateResponse = putJson(config('contento.routes.api.v1.prefix') . '/content-tags/' . $contentTagSlug, [
        'name' => 'Homepage Updated',
    ])->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $contentTagId);

    deleteJson(config('contento.routes.api.v1.prefix') . '/content-tags/' . $contentTagUpdateResponse->json(contentoResourcePath('slug')))->assertNoContent();
    assertDatabaseMissing(config('contento.table_names.content_tags'), ['id' => $contentTagId]);
});

it('resolves pages outside visibility scopes for show update and destroy routes', function () {
    $inactivePage = postJson(config('contento.routes.api.v1.prefix') . '/pages', [
        'title' => 'Inactive Page',
        'content' => ['body' => 'Hidden'],
        'active' => false,
        'published_at' => now()->subMinute()->toISOString(),
    ])->assertCreated();

    $futurePage = postJson(config('contento.routes.api.v1.prefix') . '/pages', [
        'title' => 'Future Page',
        'content' => ['body' => 'Future'],
        'visible_date_from' => now()->addDay()->toISOString(),
        'published_at' => now()->subMinute()->toISOString(),
    ])->assertCreated();

    $unpublishedPage = postJson(config('contento.routes.api.v1.prefix') . '/pages', [
        'title' => 'Unpublished Page',
        'content' => ['body' => 'Draft'],
        'published_at' => now()->addDay()->toISOString(),
    ])->assertCreated();

    getJson(config('contento.routes.api.v1.prefix') . '/pages')
        ->assertOk()
        ->assertJsonPath('meta.total', 0);

    getJson(config('contento.routes.api.v1.prefix') . '/pages/' . $inactivePage->json(contentoResourcePath('id')))
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $inactivePage->json(contentoResourcePath('id')));

    getJson(config('contento.routes.api.v1.prefix') . '/pages/' . $futurePage->json(contentoResourcePath('slug')))
        ->assertOk()
        ->assertJsonPath(contentoResourcePath('id'), $futurePage->json(contentoResourcePath('id')));

    putJson(config('contento.routes.api.v1.prefix') . '/pages/' . $inactivePage->json(contentoResourcePath('slug')), [
        'title' => 'Inactive Page Updated',
    ])->assertOk()
        ->assertJsonPath(contentoResourcePath('title'), 'Inactive Page Updated');

    putJson(config('contento.routes.api.v1.prefix') . '/pages/' . $futurePage->json(contentoResourcePath('id')), [
        'title' => 'Future Page Updated',
    ])->assertOk()
        ->assertJsonPath(contentoResourcePath('title'), 'Future Page Updated');

    deleteJson(config('contento.routes.api.v1.prefix') . '/pages/' . $unpublishedPage->json(contentoResourcePath('slug')))
        ->assertNoContent();

    assertDatabaseMissing(config('contento.table_names.pages'), [
        'id' => $unpublishedPage->json(contentoResourcePath('id')),
    ]);
});
