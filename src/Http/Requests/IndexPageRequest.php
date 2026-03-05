<?php

namespace PictaStudio\Contento\Http\Requests;

class IndexPageRequest extends IndexQueryRequest
{
    protected function filterRules(): array
    {
        return [
            'id' => ['sometimes', 'array', 'min:1'],
            'id.*' => ['integer', 'distinct', 'min:1'],
            'slug' => ['sometimes', 'string'],
            'type' => ['sometimes', 'string'],
            'active' => ['sometimes', 'boolean'],
            'important' => ['sometimes', 'boolean'],
            'visible_date_from_start' => ['sometimes', 'date'],
            'visible_date_from_end' => ['sometimes', 'date', 'after_or_equal:visible_date_from_start'],
            'visible_date_to_start' => ['sometimes', 'date'],
            'visible_date_to_end' => ['sometimes', 'date', 'after_or_equal:visible_date_to_start'],
            'published_at_start' => ['sometimes', 'date'],
            'published_at_end' => ['sometimes', 'date', 'after_or_equal:published_at_start'],
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
            'slug',
            'title',
            'type',
            'active',
            'important',
            'visible_date_from',
            'visible_date_to',
            'published_at',
            'created_at',
            'updated_at',
        ];
    }

    protected function queryAliases(): array
    {
        return [
            'is_active' => 'active',
        ];
    }
}
