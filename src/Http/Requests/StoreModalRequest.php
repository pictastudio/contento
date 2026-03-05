<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PictaStudio\Translatable\Locales;

class StoreModalRequest extends FormRequest
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
            'visible_date_from' => ['nullable', 'date'],
            'visible_date_to' => ['nullable', 'date'],
            'template' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'cta_button_text' => ['nullable', 'string'],
            'cta_button_url' => ['nullable', 'string'],
            'cta_button_color' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'timeout' => ['integer'],
            'popup_time' => ['nullable', 'string'],
            'show_on_all_pages' => ['boolean'],
            'tag_ids' => ['prohibited'],
        ];

        $localeTitleKeys = [];
        foreach (app(Locales::class)->all() as $locale) {
            $rules[$locale] = ['sometimes', 'array:title,content,cta_button_text'];
            $rules["{$locale}.title"] = ['sometimes', 'string', 'max:255'];
            $rules["{$locale}.content"] = ['nullable', 'string'];
            $rules["{$locale}.cta_button_text"] = ['nullable', 'string'];
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

        $merge = [];

        if (!$this->has('content')) {
            $merge['content'] = '';
        }

        if (!$this->has('cta_button_text')) {
            $merge['cta_button_text'] = '';
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }
}
