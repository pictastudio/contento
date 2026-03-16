<?php

namespace PictaStudio\Contento\Validations;

use PictaStudio\Contento\Validations\Concerns\InteractsWithTranslatableRules;
use PictaStudio\Contento\Validations\Contracts\MenuValidationRules;

class MenuValidation implements MenuValidationRules
{
    use InteractsWithTranslatableRules;

    public function getStoreValidationRules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
            'visible_date_from' => ['nullable', 'date'],
            'visible_date_to' => ['nullable', 'date', 'after_or_equal:visible_date_from'],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'string', 'max:255'],
            ], ['title']),
        ];
    }

    public function getUpdateValidationRules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
            'visible_date_from' => ['nullable', 'date'],
            'visible_date_to' => ['nullable', 'date', 'after_or_equal:visible_date_from'],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'string', 'max:255'],
            ], ['title']),
        ];
    }
}
