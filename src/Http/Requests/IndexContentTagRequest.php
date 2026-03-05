<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Validation\Rule;

class IndexContentTagRequest extends IndexQueryRequest
{
    protected function filterRules(): array
    {
        return [
            'id' => ['sometimes', 'array', 'min:1'],
            'id.*' => ['integer', 'distinct', 'min:1'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'slug' => ['sometimes', 'string'],
            'name' => ['sometimes', 'string'],
            'active' => ['sometimes', 'boolean'],
            'show_in_menu' => ['sometimes', 'boolean'],
            'in_evidence' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'visible_from_start' => ['sometimes', 'date'],
            'visible_from_end' => ['sometimes', 'date', 'after_or_equal:visible_from_start'],
            'visible_until_start' => ['sometimes', 'date'],
            'visible_until_end' => ['sometimes', 'date', 'after_or_equal:visible_until_start'],
            'created_at_start' => ['sometimes', 'date'],
            'created_at_end' => ['sometimes', 'date', 'after_or_equal:created_at_start'],
            'updated_at_start' => ['sometimes', 'date'],
            'updated_at_end' => ['sometimes', 'date', 'after_or_equal:updated_at_start'],
            'as_tree' => ['sometimes', 'boolean'],
            'include' => ['sometimes', 'array'],
            'include.*' => [
                'string',
                Rule::in([
                    'parent',
                    'children',
                    'content_tags',
                    'pages',
                    'faq_categories',
                    'faqs',
                ]),
            ],
        ];
    }

    protected function sortableFields(): array
    {
        return [
            'id',
            'parent_id',
            'name',
            'slug',
            'active',
            'show_in_menu',
            'in_evidence',
            'sort_order',
            'visible_from',
            'visible_until',
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

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        if (!$this->has('include')) {
            return;
        }

        $include = $this->query('include');

        if (is_string($include)) {
            $this->merge([
                'include' => collect(explode(',', $include))
                    ->map(fn (string $item): string => mb_trim($item))
                    ->filter(fn (string $item): bool => $item !== '')
                    ->values()
                    ->all(),
            ]);
        }
    }
}
