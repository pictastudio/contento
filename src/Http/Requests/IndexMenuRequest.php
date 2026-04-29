<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Validation\Rule;

class IndexMenuRequest extends IndexQueryRequest
{
    protected function filterRules(): array
    {
        return [
            'id' => ['sometimes', 'array', 'min:1'],
            'id.*' => ['integer', 'distinct', 'min:1'],
            'filter' => ['sometimes', 'string', Rule::in(['all'])],
            'title' => ['sometimes', 'string'],
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
            'include' => ['sometimes', 'array'],
            'include.*' => ['string', Rule::in(['items'])],
        ];
    }

    protected function sortableFields(): array
    {
        return [
            'id',
            'title',
            'slug',
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

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        if (!$this->has('include')) {
            return;
        }

        $include = $this->query('include');

        if (!is_string($include)) {
            return;
        }

        $this->merge([
            'include' => collect(explode(',', $include))
                ->map(fn (string $item): string => mb_trim($item))
                ->filter(fn (string $item): bool => $item !== '')
                ->values()
                ->all(),
        ]);
    }
}
