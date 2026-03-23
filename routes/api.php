<?php

use Illuminate\Support\Facades\Route;
use PictaStudio\Contento\Http\Controllers\{ContentTagController, FaqCategoryController, FaqController, MailFormController, MenuController, MenuItemController, ModalController, PageController, SettingController};

Route::apiResource('pages', PageController::class);
Route::apiResource('menus', MenuController::class);
Route::apiResource('menu-items', MenuItemController::class);
Route::apiResource('faq-categories', FaqCategoryController::class);
Route::apiResource('faqs', FaqController::class);
Route::post('faqs/bulk/upsert', [FaqController::class, 'upsertMultiple']);
Route::apiResource('mail-forms', MailFormController::class);
Route::apiResource('modals', ModalController::class);
Route::apiResource('content-tags', ContentTagController::class);

Route::get('settings', [SettingController::class, 'index']);
Route::post('settings', [SettingController::class, 'store']);
Route::post('settings/bulk/update', [SettingController::class, 'bulkUpdate']);
Route::delete('settings/{setting}', [SettingController::class, 'destroy']);
