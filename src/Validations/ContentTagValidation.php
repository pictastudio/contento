<?php

namespace PictaStudio\Contento\Validations;

use Illuminate\Validation\Rule;
use PictaStudio\Contento\Contracts\ContentTagValidationRules;
use PictaStudio\Translatable\Locales;

class ContentTagValidation implements ContentTagValidationRules
{
    public function getStoreValidationRules(): array
    {
        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists($this->tableFor('content_tags'), 'id'),
            ],
            'name' => ['sometimes', 'filled', 'string', 'max:255'],
            'slug' => ['sometimes', 'filled', 'string', 'max:255'],
            'abstract' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'active' => ['sometimes', 'boolean'],
            'show_in_menu' => ['sometimes', 'boolean'],
            'in_evidence' => ['sometimes', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'visible_from' => ['nullable', 'date'],
            'visible_until' => ['nullable', 'date', 'after_or_equal:visible_from'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'distinct', Rule::exists($this->tableFor('content_tags'), 'id')],
            ...$this->translatableLocaleRules(),
        ];
    }

    public function getUpdateValidationRules(): array
    {
        return [
            'parent_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists($this->tableFor('content_tags'), 'id'),
            ],
            'name' => ['sometimes', 'filled', 'string', 'max:255'],
            'slug' => ['sometimes', 'filled', 'string', 'max:255'],
            'abstract' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'active' => ['sometimes', 'boolean'],
            'show_in_menu' => ['sometimes', 'boolean'],
            'in_evidence' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'visible_from' => ['nullable', 'date'],
            'visible_until' => ['nullable', 'date', 'after_or_equal:visible_from'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'distinct', Rule::exists($this->tableFor('content_tags'), 'id')],
            ...$this->translatableLocaleRules(),
        ];
    }

    private function translatableLocaleRules(): array
    {
        $rules = [];

        foreach (app(Locales::class)->all() as $locale) {
            $rules[$locale] = ['sometimes', 'array:name,slug,abstract,description'];
            $rules["{$locale}.name"] = ['sometimes', 'filled', 'string', 'max:255'];
            $rules["{$locale}.slug"] = ['sometimes', 'filled', 'string', 'max:255'];
            $rules["{$locale}.abstract"] = ['sometimes', 'nullable', 'string'];
            $rules["{$locale}.description"] = ['sometimes', 'nullable', 'string'];
        }

        return $rules;
    }

    private function tableFor(string $key): string
    {
        return (string) config("contento.table_names.{$key}");
    }
}
