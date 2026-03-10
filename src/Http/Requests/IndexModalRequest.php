<?php

namespace PictaStudio\Contento\Http\Requests;

class IndexModalRequest extends IndexQueryRequest
{
    protected function filterRules(): array
    {
        return [
            'id' => ['sometimes', 'array', 'min:1'],
            'id.*' => ['integer', 'distinct', 'min:1'],
            'slug' => ['sometimes', 'string'],
            'template' => ['sometimes', 'string'],
            'popup_time' => ['sometimes', 'string'],
            'active' => ['sometimes', 'boolean'],
            'show_on_all_pages' => ['sometimes', 'boolean'],
            'timeout_min' => ['sometimes', 'integer', 'min:0'],
            'timeout_max' => ['sometimes', 'integer', 'min:0'],
            'visible_date_from' => ['sometimes', 'date'],
            'visible_date_from_start' => ['sometimes', 'date'],
            'visible_date_from_end' => ['sometimes', 'date', 'after_or_equal:visible_date_from_start'],
            'visible_date_to' => ['sometimes', 'date'],
            'visible_date_to_start' => ['sometimes', 'date'],
            'visible_date_to_end' => ['sometimes', 'date', 'after_or_equal:visible_date_to_start'],
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
            'template',
            'popup_time',
            'active',
            'show_on_all_pages',
            'timeout',
            'visible_date_from',
            'visible_date_to',
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
