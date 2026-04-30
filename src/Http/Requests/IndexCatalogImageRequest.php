<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Validation\Rule;

class IndexCatalogImageRequest extends IndexQueryRequest
{
    protected function filterRules(): array
    {
        return [
            'id' => ['sometimes', 'array', 'min:1'],
            'id.*' => ['integer', 'distinct', 'min:1'],
            'all' => ['sometimes', 'boolean'],
            'filter' => ['sometimes', 'string', Rule::in(['all'])],
            'name' => ['sometimes', 'string'],
            'title' => ['sometimes', 'string'],
            'alt' => ['sometimes', 'string'],
            'mime_type' => ['sometimes', 'string'],
            'size_min' => ['sometimes', 'integer', 'min:0'],
            'size_max' => ['sometimes', 'integer', 'min:0', 'gte:size_min'],
            'created_at_start' => ['sometimes', 'date'],
            'created_at_end' => ['sometimes', 'date', 'after_or_equal:created_at_start'],
            'updated_at_start' => ['sometimes', 'date'],
            'updated_at_end' => ['sometimes', 'date', 'after_or_equal:updated_at_start'],
        ];
    }

    protected function sortableFields(): array
    {
        return [
            'id',
            'name',
            'title',
            'alt',
            'mime_type',
            'size',
            'created_at',
            'updated_at',
        ];
    }
}
