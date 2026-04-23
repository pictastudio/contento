<?php

namespace PictaStudio\Contento\Validations;

use Illuminate\Validation\Rule;
use PictaStudio\Contento\Support\CatalogImage;
use PictaStudio\Contento\Validations\Concerns\InteractsWithTranslatableRules;
use PictaStudio\Contento\Validations\Contracts\ContentTagValidationRules;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class ContentTagValidation implements ContentTagValidationRules
{
    use InteractsWithTranslatableRules;

    public function getStoreValidationRules(): array
    {
        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists($this->tableFor('content_tag'), 'id'),
            ],
            'name' => ['sometimes', 'filled', 'string', 'max:255'],
            'slug' => ['sometimes', 'filled', 'string', 'max:255'],
            'abstract' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'images' => ['sometimes', 'nullable', 'array'],
            'images.*.id' => ['nullable', 'string', 'max:255'],
            'images.*.file' => ['sometimes', 'file', 'image'],
            'images.*.alt' => ['nullable', 'string', 'max:255'],
            'images.*.name' => ['nullable', 'string', 'max:255'],
            'images.*.mimetype' => ['nullable', 'string', 'max:255'],
            'images.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'images.*.type' => ['nullable', 'string', Rule::in(CatalogImage::TYPES)],
            'active' => ['sometimes', 'boolean'],
            'show_in_menu' => ['sometimes', 'boolean'],
            'in_evidence' => ['sometimes', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'visible_from' => ['nullable', 'date'],
            'visible_until' => ['nullable', 'date', 'after_or_equal:visible_from'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'distinct', Rule::exists($this->tableFor('content_tag'), 'id')],
            ...$this->translatableLocaleRules([
                'name' => ['sometimes', 'filled', 'string', 'max:255'],
                'slug' => ['sometimes', 'filled', 'string', 'max:255'],
                'abstract' => ['sometimes', 'nullable', 'string'],
                'description' => ['sometimes', 'nullable', 'string'],
            ], ['name', 'slug', 'abstract', 'description']),
        ];
    }

    public function getUpdateValidationRules(): array
    {
        return [
            'parent_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists($this->tableFor('content_tag'), 'id'),
            ],
            'name' => ['sometimes', 'filled', 'string', 'max:255'],
            'slug' => ['sometimes', 'filled', 'string', 'max:255'],
            'abstract' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'images' => ['sometimes', 'nullable', 'array'],
            'images.*.id' => ['nullable', 'string', 'max:255'],
            'images.*.file' => ['sometimes', 'file', 'image'],
            'images.*.alt' => ['nullable', 'string', 'max:255'],
            'images.*.name' => ['nullable', 'string', 'max:255'],
            'images.*.mimetype' => ['nullable', 'string', 'max:255'],
            'images.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'images.*.type' => ['nullable', 'string', Rule::in(CatalogImage::TYPES)],
            'active' => ['sometimes', 'boolean'],
            'show_in_menu' => ['sometimes', 'boolean'],
            'in_evidence' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'visible_from' => ['nullable', 'date'],
            'visible_until' => ['nullable', 'date', 'after_or_equal:visible_from'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'distinct', Rule::exists($this->tableFor('content_tag'), 'id')],
            ...$this->translatableLocaleRules([
                'name' => ['sometimes', 'filled', 'string', 'max:255'],
                'slug' => ['sometimes', 'filled', 'string', 'max:255'],
                'abstract' => ['sometimes', 'nullable', 'string'],
                'description' => ['sometimes', 'nullable', 'string'],
            ], ['name', 'slug', 'abstract', 'description']),
        ];
    }

    private function tableFor(string $model): string
    {
        return (new (resolve_model($model)))->getTable();
    }
}
