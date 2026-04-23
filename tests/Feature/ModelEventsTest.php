<?php

use Illuminate\Support\Facades\Event;
use PictaStudio\Contento\Events\{ContentTagCreated, ContentTagDeleted, ContentTagUpdated, FaqCategoryCreated, FaqCategoryDeleted, FaqCategoryUpdated, FaqCreated, FaqDeleted, FaqUpdated, MailFormCreated, MailFormDeleted, MailFormUpdated, MenuCreated, MenuDeleted, MenuItemCreated, MenuItemDeleted, MenuItemUpdated, MenuUpdated, MetadataCreated, MetadataDeleted, MetadataUpdated, ModalCreated, ModalDeleted, ModalUpdated, PageCreated, PageDeleted, PageUpdated, SettingCreated, SettingDeleted, SettingUpdated};
use PictaStudio\Contento\Models\{ContentTag, Faq, FaqCategory, MailForm, Menu, MenuItem, Metadata, Modal, Page, Setting};

it('dispatches created updated and deleted events for content models', function (callable $createModel, callable $updateModel, string $createdEvent, string $updatedEvent, string $deletedEvent) {
    Event::fake([$createdEvent, $updatedEvent, $deletedEvent]);

    $model = $createModel();
    Event::assertDispatched($createdEvent);

    $updateModel($model);
    Event::assertDispatched($updatedEvent);

    $model->delete();
    Event::assertDispatched($deletedEvent);
})->with([
    'page' => [
        fn () => Page::factory()->create(),
        fn (Page $page) => $page->update(['title' => 'Updated page']),
        PageCreated::class,
        PageUpdated::class,
        PageDeleted::class,
    ],
    'menu' => [
        fn () => Menu::factory()->create(),
        fn (Menu $menu) => $menu->update(['title' => 'Updated menu']),
        MenuCreated::class,
        MenuUpdated::class,
        MenuDeleted::class,
    ],
    'menu item' => [
        fn () => MenuItem::factory()->create(),
        fn (MenuItem $menuItem) => $menuItem->update(['title' => 'Updated item']),
        MenuItemCreated::class,
        MenuItemUpdated::class,
        MenuItemDeleted::class,
    ],
    'faq category' => [
        fn () => FaqCategory::factory()->create(),
        fn (FaqCategory $faqCategory) => $faqCategory->update(['title' => 'Updated category']),
        FaqCategoryCreated::class,
        FaqCategoryUpdated::class,
        FaqCategoryDeleted::class,
    ],
    'faq' => [
        fn () => Faq::factory()->create(),
        fn (Faq $faq) => $faq->update(['title' => 'Updated faq']),
        FaqCreated::class,
        FaqUpdated::class,
        FaqDeleted::class,
    ],
    'mail form' => [
        fn () => MailForm::factory()->create(),
        fn (MailForm $mailForm) => $mailForm->update(['name' => 'Updated form']),
        MailFormCreated::class,
        MailFormUpdated::class,
        MailFormDeleted::class,
    ],
    'modal' => [
        fn () => Modal::factory()->create(),
        fn (Modal $modal) => $modal->update(['title' => 'Updated modal']),
        ModalCreated::class,
        ModalUpdated::class,
        ModalDeleted::class,
    ],
    'content tag' => [
        fn () => ContentTag::factory()->create(),
        fn (ContentTag $contentTag) => $contentTag->update(['name' => 'Updated tag']),
        ContentTagCreated::class,
        ContentTagUpdated::class,
        ContentTagDeleted::class,
    ],
    'metadata' => [
        fn () => Metadata::factory()->create(),
        fn (Metadata $metadata) => $metadata->update(['name' => 'Updated metadata']),
        MetadataCreated::class,
        MetadataUpdated::class,
        MetadataDeleted::class,
    ],
    'setting' => [
        fn () => Setting::factory()->create(),
        fn (Setting $setting) => $setting->update(['value' => 'Updated setting']),
        SettingCreated::class,
        SettingUpdated::class,
        SettingDeleted::class,
    ],
]);
