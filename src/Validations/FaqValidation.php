<?php

namespace PictaStudio\Contento\Validations;

use Illuminate\Validation\Rule;
use PictaStudio\Contento\Validations\Concerns\InteractsWithTranslatableRules;
use PictaStudio\Contento\Validations\Contracts\FaqValidationRules;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class FaqValidation implements FaqValidationRules
{
    use InteractsWithTranslatableRules;

    public function getStoreValidationRules(): array
    {
        return [
            'faq_category_id' => ['nullable', Rule::exists($this->tableFor('faq_category'), 'id')],
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'filled', 'string', 'max:255'],
            'active' => ['boolean'],
            'visible_date_from' => ['nullable', 'date'],
            'visible_date_to' => ['nullable', 'date'],
            'content' => ['nullable', 'string'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', Rule::exists($this->tableFor('content_tag'), 'id')],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'string', 'max:255'],
                'slug' => ['sometimes', 'filled', 'string', 'max:255'],
                'content' => ['sometimes', 'nullable', 'string'],
            ], ['title', 'slug', 'content']),
        ];
    }

    public function getUpdateValidationRules(): array
    {
        return [
            'faq_category_id' => ['nullable', Rule::exists($this->tableFor('faq_category'), 'id')],
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'filled', 'string', 'max:255'],
            'active' => ['boolean'],
            'visible_date_from' => ['nullable', 'date'],
            'visible_date_to' => ['nullable', 'date'],
            'content' => ['nullable', 'string'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', Rule::exists($this->tableFor('content_tag'), 'id')],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'string', 'max:255'],
                'slug' => ['sometimes', 'filled', 'string', 'max:255'],
                'content' => ['sometimes', 'nullable', 'string'],
            ], ['title', 'slug', 'content']),
        ];
    }

    public function getBulkUpsertValidationRules(): array
    {
        $rules = [
            'faqs' => ['required', 'array', 'min:1'],
            'faqs.*' => ['required', 'array'],
            'faqs.*.id' => ['nullable', 'integer'],
            'faqs.*.faq_category_id' => ['nullable', Rule::exists($this->tableFor('faq_category'), 'id')],
            'faqs.*.title' => ['sometimes', 'string', 'max:255'],
            'faqs.*.slug' => ['sometimes', 'filled', 'string', 'max:255'],
            'faqs.*.active' => ['boolean'],
            'faqs.*.visible_date_from' => ['nullable', 'date'],
            'faqs.*.visible_date_to' => ['nullable', 'date'],
            'faqs.*.content' => ['nullable', 'string'],
            'faqs.*.tag_ids' => ['nullable', 'array'],
            'faqs.*.tag_ids.*' => ['integer', Rule::exists($this->tableFor('content_tag'), 'id')],
        ];

        foreach ($this->translatableLocales() as $locale) {
            $rules["faqs.*.{$locale}.title"] = ['sometimes', 'string', 'max:255'];
            $rules["faqs.*.{$locale}.slug"] = ['sometimes', 'filled', 'string', 'max:255'];
            $rules["faqs.*.{$locale}.content"] = ['sometimes', 'nullable', 'string'];
        }

        return $rules;
    }

    private function tableFor(string $model): string
    {
        return (new (resolve_model($model)))->getTable();
    }
}
