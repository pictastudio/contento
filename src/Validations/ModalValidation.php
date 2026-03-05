<?php

namespace PictaStudio\Contento\Validations;

use PictaStudio\Contento\Validations\Concerns\InteractsWithTranslatableRules;
use PictaStudio\Contento\Validations\Contracts\ModalValidationRules;

class ModalValidation implements ModalValidationRules
{
    use InteractsWithTranslatableRules;

    public function getStoreValidationRules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'active' => ['boolean'],
            'visible_date_from' => ['nullable', 'date'],
            'visible_date_to' => ['nullable', 'date'],
            'template' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'cta_button_text' => ['nullable', 'string'],
            'cta_button_url' => ['nullable', 'string'],
            'cta_button_color' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'timeout' => ['integer'],
            'popup_time' => ['nullable', 'string'],
            'show_on_all_pages' => ['boolean'],
            'tag_ids' => ['prohibited'],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'string', 'max:255'],
                'content' => ['sometimes', 'nullable', 'string'],
                'cta_button_text' => ['sometimes', 'nullable', 'string'],
            ], ['title', 'content', 'cta_button_text']),
        ];
    }

    public function getUpdateValidationRules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'active' => ['boolean'],
            'visible_date_from' => ['nullable', 'date'],
            'visible_date_to' => ['nullable', 'date'],
            'template' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'cta_button_text' => ['nullable', 'string'],
            'cta_button_url' => ['nullable', 'string'],
            'cta_button_color' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'timeout' => ['integer'],
            'popup_time' => ['nullable', 'string'],
            'show_on_all_pages' => ['boolean'],
            'tag_ids' => ['prohibited'],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'string', 'max:255'],
                'content' => ['sometimes', 'nullable', 'string'],
                'cta_button_text' => ['sometimes', 'nullable', 'string'],
            ], ['title', 'content', 'cta_button_text']),
        ];
    }
}
