<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use PictaStudio\Translatable\Locales;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['string', 'max:255'],
            'type' => ['nullable', 'string'],
            'active' => ['boolean'],
            'important' => ['boolean'],
            'visible_date_from' => ['nullable', 'date'],
            'visible_date_to' => ['nullable', 'date'],
            'published_at' => ['nullable', 'date'],
            'author' => ['nullable', 'string'],
            'abstract' => ['nullable', 'string'],
            'content' => ['nullable', 'array'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', Rule::exists((string) config('contento.table_names.content_tags'), 'id')],
        ];

        $localeTitleKeys = [];
        foreach (app(Locales::class)->all() as $locale) {
            $rules[$locale] = ['sometimes', 'array:title,abstract'];
            $rules["{$locale}.title"] = ['sometimes', 'string', 'max:255'];
            $rules["{$locale}.abstract"] = ['nullable', 'string'];
            $localeTitleKeys[] = "{$locale}.title";
        }

        if ($this->isMethod('post')) {
            $titleRequiredRule = empty($localeTitleKeys)
                ? 'required'
                : 'required_without_all:' . implode(',', $localeTitleKeys);
            array_unshift($rules['title'], $titleRequiredRule);
        } else {
            array_unshift($rules['title'], 'sometimes');
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->isMethod('post')) {
            return;
        }

        if (!$this->has('abstract')) {
            $this->merge(['abstract' => '']);
        }
    }
}
