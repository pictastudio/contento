<?php

use Illuminate\Support\Facades\Route;
use PictaStudio\Contento\Http\Controllers\{FaqCategoryController, FaqController, MailFormController, ModalController, PageController, SettingController};

Route::group([
    'prefix' => config('contento.prefix', 'api/contento/v1'),
    'middleware' => config('contento.middleware', ['api']),
], function () {
    Route::apiResource('pages', PageController::class);
    Route::apiResource('faq-categories', FaqCategoryController::class);
    Route::apiResource('faqs', FaqController::class);
    Route::apiResource('mail-forms', MailFormController::class);
    Route::apiResource('modals', ModalController::class);

    Route::get('settings', [SettingController::class, 'index']);
    Route::post('settings', [SettingController::class, 'store']);
    Route::delete('settings/{setting}', [SettingController::class, 'destroy']);
});
