<?php

namespace PictaStudio\Contento\Helpers\Functions;

use Illuminate\Database\Eloquent\{Builder, Model};
use InvalidArgumentException;
use PictaStudio\Contento\Models\{ContentTag, Faq, FaqCategory, MailForm, Menu, MenuItem, Modal, Page, Setting};

if (!function_exists('resolve_model')) {
    /**
     * Resolve the configured model class.
     *
     * @param  string  $model  One of: 'page', 'menu', 'menu_item', 'faq_category', 'faq', 'mail_form', 'modal', 'content_tag', 'setting', 'user'.
     */
    function resolve_model(string $model): string
    {
        $resolved = config('contento.models.' . $model);

        if (is_string($resolved) && class_exists($resolved)) {
            return $resolved;
        }

        if ($model === 'user') {
            $authUserModel = config('auth.providers.users.model');

            if (is_string($authUserModel) && class_exists($authUserModel)) {
                return $authUserModel;
            }
        }

        return match ($model) {
            'page' => Page::class,
            'menu' => Menu::class,
            'menu_item' => MenuItem::class,
            'faq_category' => FaqCategory::class,
            'faq' => Faq::class,
            'mail_form' => MailForm::class,
            'modal' => Modal::class,
            'content_tag' => ContentTag::class,
            'setting' => Setting::class,
            'user' => throw new InvalidArgumentException(
                'Unsupported contento model [user]. Configure contento.models.user or auth.providers.users.model.'
            ),
            default => throw new InvalidArgumentException("Unsupported contento model [{$model}]."),
        };
    }
}

if (!function_exists('query')) {
    /**
     * Initialize a query builder for the given model.
     *
     * @param  string  $model  One of: 'page', 'menu', 'menu_item', 'faq_category', 'faq', 'mail_form', 'modal', 'content_tag', 'setting', 'user'.
     */
    function query(string $model): Builder
    {
        return resolve_model($model)::query();
    }
}

if (!function_exists('get_fresh_model_instance')) {
    /**
     * Get a fresh model instance for the given model key.
     *
     * @param  string  $model  One of: 'page', 'menu', 'menu_item', 'faq_category', 'faq', 'mail_form', 'modal', 'content_tag', 'setting', 'user'.
     */
    function get_fresh_model_instance(string $model): Model
    {
        return (new (resolve_model($model)))->updateTimestamps();
    }
}
