<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use PictaStudio\Translatable\Locales;

class StoreFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'faq_category_id' => [
                'nullable',
                Rule::exists((string) config('contento.table_names.faq_categories'), 'id'),
            ],
            'title' => ['string', 'max:255'],
            'active' => ['boolean'],
            'visible_date_from' => ['nullable', 'date'],
            'visible_date_to' => ['nullable', 'date'],
            'content' => ['nullable', 'string'],
        ];

        $localeTitleKeys = [];
        foreach (app(Locales::class)->all() as $locale) {
            $rules[$locale] = ['sometimes', 'array'];
            $rules["{$locale}.title"] = ['sometimes', 'string', 'max:255'];
            $rules["{$locale}.content"] = ['nullable', 'string'];
            $localeTitleKeys[] = "{$locale}.title";
        }

        $titleRequiredRule = empty($localeTitleKeys)
            ? 'required'
            : 'required_without_all:' . implode(',', $localeTitleKeys);
        array_unshift($rules['title'], $titleRequiredRule);

        return $rules;
    }
}
