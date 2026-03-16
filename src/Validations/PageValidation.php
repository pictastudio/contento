<?php

namespace PictaStudio\Contento\Validations;

use Illuminate\Validation\Rule;
use PictaStudio\Contento\Validations\Concerns\InteractsWithTranslatableRules;
use PictaStudio\Contento\Validations\Contracts\PageValidationRules;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class PageValidation implements PageValidationRules
{
    use InteractsWithTranslatableRules;

    public function getStoreValidationRules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'filled', 'string', 'max:255'],
            'type' => ['nullable', 'string'],
            'active' => ['boolean'],
            'important' => ['boolean'],
            'visible_date_from' => ['nullable', 'date'],
            'visible_date_to' => ['nullable', 'date'],
            'published_at' => ['nullable', 'date'],
            'author' => ['nullable', 'string'],
            'abstract' => ['nullable', 'string'],
            'content' => ['nullable', 'array'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', Rule::exists($this->tableFor('content_tag'), 'id')],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'string', 'max:255'],
                'slug' => ['sometimes', 'filled', 'string', 'max:255'],
                'abstract' => ['sometimes', 'nullable', 'string'],
                'content' => ['sometimes', 'nullable', 'array'],
            ], ['title', 'slug', 'abstract', 'content']),
        ];
    }

    public function getUpdateValidationRules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'filled', 'string', 'max:255'],
            'type' => ['nullable', 'string'],
            'active' => ['boolean'],
            'important' => ['boolean'],
            'visible_date_from' => ['nullable', 'date'],
            'visible_date_to' => ['nullable', 'date'],
            'published_at' => ['nullable', 'date'],
            'author' => ['nullable', 'string'],
            'abstract' => ['nullable', 'string'],
            'content' => ['nullable', 'array'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', Rule::exists($this->tableFor('content_tag'), 'id')],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'string', 'max:255'],
                'slug' => ['sometimes', 'filled', 'string', 'max:255'],
                'abstract' => ['sometimes', 'nullable', 'string'],
                'content' => ['sometimes', 'nullable', 'array'],
            ], ['title', 'slug', 'abstract', 'content']),
        ];
    }

    private function tableFor(string $model): string
    {
        return (new (resolve_model($model)))->getTable();
    }
}
