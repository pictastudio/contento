<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreModalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
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
        ];
    }
}
