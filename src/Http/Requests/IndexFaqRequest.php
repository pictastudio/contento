<?php

namespace PictaStudio\Contento\Http\Requests;

class IndexFaqRequest extends IndexQueryRequest
{
    protected function filterRules(): array
    {
        return [
            'id' => ['sometimes', 'array', 'min:1'],
            'id.*' => ['integer', 'distinct', 'min:1'],
            'faq_category_id' => ['sometimes', 'integer', 'min:1'],
            'slug' => ['sometimes', 'string'],
            'active' => ['sometimes', 'boolean'],
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
            'faq_category_id',
            'slug',
            'title',
            'active',
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
