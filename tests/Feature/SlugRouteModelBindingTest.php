<?php

use PictaStudio\Contento\Models\FaqCategory;

use function Pest\Laravel\{assertDatabaseMissing, deleteJson, getJson, postJson, putJson};

it('resolves slug-enabled resources by slug for show, update, and destroy', function () {
    $pageResponse = postJson(config('contento.prefix') . '/pages', [
        'title' => 'Landing Page',
        'content' => ['body' => 'Page body'],
    ])->assertCreated();

    $pageSlug = $pageResponse->json('data.slug');
    $pageId = $pageResponse->json('data.id');

    getJson(config('contento.prefix') . '/pages/' . $pageSlug)
        ->assertOk()
        ->assertJsonPath('data.id', $pageId);

    $pageUpdateResponse = putJson(config('contento.prefix') . '/pages/' . $pageSlug, [
        'title' => 'Landing Page Updated',
    ])->assertOk()
        ->assertJsonPath('data.id', $pageId);

    deleteJson(config('contento.prefix') . '/pages/' . $pageUpdateResponse->json('data.slug'))->assertNoContent();
    assertDatabaseMissing(config('contento.table_names.pages'), ['id' => $pageId]);

    $categoryResponse = postJson(config('contento.prefix') . '/faq-categories', [
        'title' => 'General Category',
    ])->assertCreated();

    $categorySlug = $categoryResponse->json('data.slug');
    $categoryId = $categoryResponse->json('data.id');

    getJson(config('contento.prefix') . '/faq-categories/' . $categorySlug)
        ->assertOk()
        ->assertJsonPath('data.id', $categoryId);

    $categoryUpdateResponse = putJson(config('contento.prefix') . '/faq-categories/' . $categorySlug, [
        'title' => 'General Category Updated',
    ])->assertOk()
        ->assertJsonPath('data.id', $categoryId);

    deleteJson(config('contento.prefix') . '/faq-categories/' . $categoryUpdateResponse->json('data.slug'))->assertNoContent();
    assertDatabaseMissing(config('contento.table_names.faq_categories'), ['id' => $categoryId]);

    $faqCategory = FaqCategory::factory()->create();

    $faqResponse = postJson(config('contento.prefix') . '/faqs', [
        'faq_category_id' => $faqCategory->getKey(),
        'title' => 'How it works',
        'content' => 'Faq answer',
    ])->assertCreated();

    $faqSlug = $faqResponse->json('data.slug');
    $faqId = $faqResponse->json('data.id');

    getJson(config('contento.prefix') . '/faqs/' . $faqSlug)
        ->assertOk()
        ->assertJsonPath('data.id', $faqId);

    $faqUpdateResponse = putJson(config('contento.prefix') . '/faqs/' . $faqSlug, [
        'title' => 'How it works updated',
    ])->assertOk()
        ->assertJsonPath('data.id', $faqId);

    deleteJson(config('contento.prefix') . '/faqs/' . $faqUpdateResponse->json('data.slug'))->assertNoContent();
    assertDatabaseMissing(config('contento.table_names.faqs'), ['id' => $faqId]);

    $mailFormResponse = postJson(config('contento.prefix') . '/mail-forms', [
        'name' => 'Support Request',
        'email_to' => 'support@example.com',
    ])->assertCreated();

    $mailFormSlug = $mailFormResponse->json('data.slug');
    $mailFormId = $mailFormResponse->json('data.id');

    getJson(config('contento.prefix') . '/mail-forms/' . $mailFormSlug)
        ->assertOk()
        ->assertJsonPath('data.id', $mailFormId);

    $mailFormUpdateResponse = putJson(config('contento.prefix') . '/mail-forms/' . $mailFormSlug, [
        'name' => 'Support Team',
    ])->assertOk()
        ->assertJsonPath('data.id', $mailFormId);

    deleteJson(config('contento.prefix') . '/mail-forms/' . $mailFormUpdateResponse->json('data.slug'))->assertNoContent();
    assertDatabaseMissing(config('contento.table_names.mail_forms'), ['id' => $mailFormId]);

    $modalResponse = postJson(config('contento.prefix') . '/modals', [
        'title' => 'Newsletter Popup',
        'content' => 'Join our newsletter',
    ])->assertCreated();

    $modalSlug = $modalResponse->json('data.slug');
    $modalId = $modalResponse->json('data.id');

    getJson(config('contento.prefix') . '/modals/' . $modalSlug)
        ->assertOk()
        ->assertJsonPath('data.id', $modalId);

    $modalUpdateResponse = putJson(config('contento.prefix') . '/modals/' . $modalSlug, [
        'title' => 'Newsletter Popup Updated',
    ])->assertOk()
        ->assertJsonPath('data.id', $modalId);

    deleteJson(config('contento.prefix') . '/modals/' . $modalUpdateResponse->json('data.slug'))->assertNoContent();
    assertDatabaseMissing(config('contento.table_names.modals'), ['id' => $modalId]);
});
