<?php

namespace PictaStudio\Contento\Validations;

use Illuminate\Validation\Rule;
use PictaStudio\Contento\Validations\Concerns\InteractsWithTranslatableRules;
use PictaStudio\Contento\Validations\Contracts\FaqCategoryValidationRules;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class FaqCategoryValidation implements FaqCategoryValidationRules
{
    use InteractsWithTranslatableRules;

    public function getStoreValidationRules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'filled', 'string', 'max:255'],
            'active' => ['boolean'],
            'abstract' => ['nullable', 'string'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', Rule::exists($this->tableFor('content_tag'), 'id')],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'string', 'max:255'],
                'slug' => ['sometimes', 'filled', 'string', 'max:255'],
                'abstract' => ['sometimes', 'nullable', 'string'],
            ], ['title', 'slug', 'abstract']),
        ];
    }

    public function getUpdateValidationRules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'filled', 'string', 'max:255'],
            'active' => ['boolean'],
            'abstract' => ['nullable', 'string'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', Rule::exists($this->tableFor('content_tag'), 'id')],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'string', 'max:255'],
                'slug' => ['sometimes', 'filled', 'string', 'max:255'],
                'abstract' => ['sometimes', 'nullable', 'string'],
            ], ['title', 'slug', 'abstract']),
        ];
    }

    private function tableFor(string $model): string
    {
        return (new (resolve_model($model)))->getTable();
    }
}
