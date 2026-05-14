<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Validation\Rule;

class IndexGalleryRequest extends IndexQueryRequest
{
    protected function filterRules(): array
    {
        return [
            'id' => ['sometimes', 'array', 'min:1'],
            'id.*' => ['integer', 'distinct', 'min:1'],
            'all' => ['sometimes', 'boolean'],
            'filter' => ['sometimes', 'string', Rule::in(['all'])],
            'title' => ['sometimes', 'string'],
            'slug' => ['sometimes', 'string'],
            'code' => ['sometimes', 'string'],
            'active' => ['sometimes', 'boolean'],
            'created_at_start' => ['sometimes', 'date'],
            'created_at_end' => ['sometimes', 'date', 'after_or_equal:created_at_start'],
            'updated_at_start' => ['sometimes', 'date'],
            'updated_at_end' => ['sometimes', 'date', 'after_or_equal:updated_at_start'],
            'include' => ['sometimes', 'array'],
            'include.*' => ['string', Rule::in(['items', 'gallery_items'])],
        ];
    }

    protected function sortableFields(): array
    {
        return [
            'id',
            'title',
            'slug',
            'code',
            'active',
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
