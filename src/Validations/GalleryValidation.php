<?php

namespace PictaStudio\Contento\Validations;

use Illuminate\Validation\Rule;
use PictaStudio\Contento\Validations\Concerns\InteractsWithTranslatableRules;
use PictaStudio\Contento\Validations\Contracts\GalleryValidationRules;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class GalleryValidation implements GalleryValidationRules
{
    use InteractsWithTranslatableRules;

    public function getStoreValidationRules(): array
    {
        return [
            'title' => ['sometimes', 'filled', 'string', 'max:255'],
            'slug' => ['sometimes', 'filled', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique($this->tableFor('gallery'), 'code')],
            'abstract' => ['nullable', 'string'],
            'active' => ['sometimes', 'boolean'],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'filled', 'string', 'max:255'],
                'slug' => ['sometimes', 'filled', 'string', 'max:255'],
                'abstract' => ['sometimes', 'nullable', 'string'],
            ], ['title', 'slug', 'abstract']),
        ];
    }

    public function getUpdateValidationRules(): array
    {
        return [
            'title' => ['sometimes', 'filled', 'string', 'max:255'],
            'slug' => ['sometimes', 'filled', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:255', Rule::unique($this->tableFor('gallery'), 'code')->ignore(request()->route('gallery'))],
            'abstract' => ['sometimes', 'nullable', 'string'],
            'active' => ['sometimes', 'boolean'],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'filled', 'string', 'max:255'],
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
