<?php

namespace PictaStudio\Contento\Http\Requests;

class IndexSettingRequest extends IndexQueryRequest
{
    protected function filterRules(): array
    {
        return [
            'id' => ['sometimes', 'array', 'min:1'],
            'id.*' => ['integer', 'distinct', 'min:1'],
            'group' => ['sometimes', 'string'],
            'name' => ['sometimes', 'string'],
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
            'group',
            'name',
            'created_at',
            'updated_at',
        ];
    }
}
