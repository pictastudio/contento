<?php

namespace PictaStudio\Contento\Http\Requests;

class IndexMailFormRequest extends IndexQueryRequest
{
    protected function filterRules(): array
    {
        return [
            'id' => ['sometimes', 'array', 'min:1'],
            'id.*' => ['integer', 'distinct', 'min:1'],
            'name' => ['sometimes', 'string'],
            'slug' => ['sometimes', 'string'],
            'email_to' => ['sometimes', 'string'],
            'newsletter' => ['sometimes', 'boolean'],
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
            'slug',
            'email_to',
            'newsletter',
            'created_at',
            'updated_at',
        ];
    }
}
