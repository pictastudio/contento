<?php

use Illuminate\Support\Facades\Route;
use PictaStudio\Contento\Http\Controllers\{CatalogImageController, ContentTagController, FaqCategoryController, FaqController, MailFormController, MenuController, MenuItemController, MetadataController, ModalController, PageController, SettingController};

use function PictaStudio\Contento\Helpers\Functions\query;

Route::bind('catalog_image', fn (mixed $value) => query('catalog_image')->whereKey($value)->firstOrFail());

Route::apiResource('pages', PageController::class);
Route::apiResource('menus', MenuController::class);
Route::apiResource('menu-items', MenuItemController::class);
Route::post('menu-items/bulk/upsert', [MenuItemController::class, 'upsertMultiple']);
Route::apiResource('faq-categories', FaqCategoryController::class);
Route::apiResource('faqs', FaqController::class);
Route::post('faqs/bulk/upsert', [FaqController::class, 'upsertMultiple']);
Route::apiResource('mail-forms', MailFormController::class);
Route::apiResource('modals', ModalController::class);
Route::patch('content-tags/bulk/update', [ContentTagController::class, 'updateMultiple']);
Route::apiResource('content-tags', ContentTagController::class);
Route::apiResource('catalog-images', CatalogImageController::class);
Route::apiResource('metadata', MetadataController::class)->parameters([
    'metadata' => 'metadata',
]);

Route::get('settings', [SettingController::class, 'index']);
Route::post('settings', [SettingController::class, 'store']);
Route::post('settings/bulk/update', [SettingController::class, 'bulkUpdate']);
Route::delete('settings/{setting}', [SettingController::class, 'destroy']);
