<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Validation\Rule;

class IndexGalleryItemRequest extends IndexQueryRequest
{
    protected function filterRules(): array
    {
        return [
            'id' => ['sometimes', 'array', 'min:1'],
            'id.*' => ['integer', 'distinct', 'min:1'],
            'all' => ['sometimes', 'boolean'],
            'filter' => ['sometimes', 'string', Rule::in(['all'])],
            'gallery_id' => ['sometimes', 'integer', 'min:1'],
            'title' => ['sometimes', 'string'],
            'active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'visible_from' => ['sometimes', 'date'],
            'visible_from_start' => ['sometimes', 'date'],
            'visible_from_end' => ['sometimes', 'date', 'after_or_equal:visible_from_start'],
            'visible_until' => ['sometimes', 'date'],
            'visible_until_start' => ['sometimes', 'date'],
            'visible_until_end' => ['sometimes', 'date', 'after_or_equal:visible_until_start'],
            'created_at_start' => ['sometimes', 'date'],
            'created_at_end' => ['sometimes', 'date', 'after_or_equal:created_at_start'],
            'updated_at_start' => ['sometimes', 'date'],
            'updated_at_end' => ['sometimes', 'date', 'after_or_equal:updated_at_start'],
            'include' => ['sometimes', 'array'],
            'include.*' => ['string', Rule::in(['gallery'])],
        ];
    }

    protected function sortableFields(): array
    {
        return [
            'id',
            'gallery_id',
            'title',
            'active',
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
        $this->prepareIncludeQuery();
    }

    private function prepareIncludeQuery(): void
    {
        if (!$this->has('include') || !is_string($this->query('include'))) {
            return;
        }

        $this->merge([
            'include' => collect(explode(',', (string) $this->query('include')))
                ->map(fn (string $item): string => mb_trim($item))
                ->filter(fn (string $item): bool => $item !== '')
                ->values()
                ->all(),
        ]);
    }
}
