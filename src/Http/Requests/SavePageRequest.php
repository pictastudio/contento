<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string'],
            'active' => ['boolean'],
            'important' => ['boolean'],
            'visible_date_from' => ['nullable', 'date'],
            'visible_date_to' => ['nullable', 'date'],
            'published_at' => ['nullable', 'date'],
            'author' => ['nullable', 'string'],
            'abstract' => ['nullable', 'string'],
            'content' => ['nullable', 'array'],
        ];
    }
}
