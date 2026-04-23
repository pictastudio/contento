<?php

namespace PictaStudio\Contento\Validations;

use Illuminate\Validation\Rule;
use PictaStudio\Contento\Validations\Contracts\MetadataValidationRules;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class MetadataValidation implements MetadataValidationRules
{
    public function getStoreValidationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['sometimes', 'filled', 'string', 'max:255', Rule::unique($this->tableFor('metadata'), 'slug')],
            'uri' => ['required', 'string', 'max:255', Rule::unique($this->tableFor('metadata'), 'uri')],
            'metadata' => ['required', 'array'],
        ];
    }

    public function getUpdateValidationRules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'filled', 'string', 'max:255'],
            'uri' => ['sometimes', 'string', 'max:255'],
            'metadata' => ['sometimes', 'array'],
        ];
    }

    private function tableFor(string $model): string
    {
        return (new (resolve_model($model)))->getTable();
    }
}
