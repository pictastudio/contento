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
            'sort_order' => ['sometimes', 'integer', 'min:1'],
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
            'sort_order' => ['sometimes', 'integer', 'min:1'],
            'visible_date_from' => ['nullable', 'date'],
            'visible_date_to' => ['nullable', 'date', 'after_or_equal:visible_date_from'],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'string', 'max:255'],
                'link' => ['sometimes', 'nullable', 'string', 'max:65535'],
            ], ['title', 'link']),
        ];
    }

    public function getBulkUpsertValidationRules(): array
    {
        $rules = [
            'menu_items' => ['required', 'array', 'min:1'],
            'menu_items.*' => ['required', 'array'],
            'menu_items.*.id' => ['nullable', 'integer'],
            'menu_items.*.menu_id' => ['sometimes', 'integer', Rule::exists($this->tableFor('menu'), 'id')],
            'menu_items.*.parent_id' => ['sometimes', 'nullable', 'integer', Rule::exists($this->tableFor('menu_item'), 'id')],
            'menu_items.*.title' => ['sometimes', 'string', 'max:255'],
            'menu_items.*.link' => ['nullable', 'string', 'max:65535'],
            'menu_items.*.active' => ['sometimes', 'boolean'],
            'menu_items.*.sort_order' => ['sometimes', 'integer', 'min:1'],
            'menu_items.*.visible_date_from' => ['nullable', 'date'],
            'menu_items.*.visible_date_to' => ['nullable', 'date'],
        ];

        foreach ($this->translatableLocales() as $locale) {
            $rules["menu_items.*.{$locale}.title"] = ['sometimes', 'string', 'max:255'];
            $rules["menu_items.*.{$locale}.link"] = ['sometimes', 'nullable', 'string', 'max:65535'];
        }

        return $rules;
    }

    private function tableFor(string $model): string
    {
        return (new (resolve_model($model)))->getTable();
    }
}
