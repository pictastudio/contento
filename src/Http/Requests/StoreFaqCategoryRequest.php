<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PictaStudio\Translatable\Locales;

class StoreFaqCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['string', 'max:255'],
            'active' => ['boolean'],
            'abstract' => ['nullable', 'string'],
        ];

        $localeTitleKeys = [];
        foreach (app(Locales::class)->all() as $locale) {
            $rules[$locale] = ['sometimes', 'array'];
            $rules["{$locale}.title"] = ['sometimes', 'string', 'max:255'];
            $rules["{$locale}.abstract"] = ['nullable', 'string'];
            $localeTitleKeys[] = "{$locale}.title";
        }

        $titleRequiredRule = empty($localeTitleKeys)
            ? 'required'
            : 'required_without_all:' . implode(',', $localeTitleKeys);
        array_unshift($rules['title'], $titleRequiredRule);

        return $rules;
    }
}
