<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'faq_category_id' => [
                'nullable',
                Rule::exists((string) config('contento.table_names.faq_categories'), 'id'),
            ],
            'title' => ['required', 'string', 'max:255'],
            'active' => ['boolean'],
            'visible_date_from' => ['nullable', 'date'],
            'visible_date_to' => ['nullable', 'date'],
            'content' => ['nullable', 'string'],
        ];
    }
}
