<?php

namespace PictaStudio\Contento\Validations;

use Illuminate\Validation\Rule;
use PictaStudio\Contento\Validations\Concerns\InteractsWithTranslatableRules;
use PictaStudio\Contento\Validations\Contracts\MenuItemValidationRules;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class MenuItemValidation implements MenuItemValidationRules
{
    use InteractsWithTranslatableRules;

    public function getStoreValidationRules(): array
    {
        return [
            'menu_id' => ['required', 'integer', Rule::exists($this->tableFor('menu'), 'id')],
            'parent_id' => ['nullable', 'integer', Rule::exists($this->tableFor('menu_item'), 'id')],
            'title' => ['sometimes', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:65535'],
            'active' => ['sometimes', 'boolean'],
            'visible_date_from' => ['nullable', 'date'],
            'visible_date_to' => ['nullable', 'date', 'after_or_equal:visible_date_from'],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'string', 'max:255'],
                'link' => ['sometimes', 'nullable', 'string', 'max:65535'],
            ], ['title', 'link']),
        ];
    }

    public function getUpdateValidationRules(): array
    {
        return [
            'menu_id' => ['sometimes', 'integer', Rule::exists($this->tableFor('menu'), 'id')],
            'parent_id' => ['sometimes', 'nullable', 'integer', Rule::exists($this->tableFor('menu_item'), 'id')],
            'title' => ['sometimes', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:65535'],
            'active' => ['sometimes', 'boolean'],
            'visible_date_from' => ['nullable', 'date'],
            'visible_date_to' => ['nullable', 'date', 'after_or_equal:visible_date_from'],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'string', 'max:255'],
                'link' => ['sometimes', 'nullable', 'string', 'max:65535'],
            ], ['title', 'link']),
        ];
    }

    private function tableFor(string $model): string
    {
        return (new (resolve_model($model)))->getTable();
    }
}
