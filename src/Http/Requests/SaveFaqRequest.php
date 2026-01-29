<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use PictaStudio\Contento\Models\Faq;

class SaveFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tableName = (string) config('contento.table_names.faqs');
        $faq = $this->route('faq');
        $id = $faq instanceof Faq ? $faq->getKey() : $faq;

        return [
            'faq_category_id' => [
                'required',
                Rule::exists((string) config('contento.table_names.faq_categories'), 'id'),
            ],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique($tableName, 'slug')->ignore($id),
            ],
            'active' => ['boolean'],
            'visible_date_from' => ['nullable', 'date'],
            'visible_date_to' => ['nullable', 'date'],
            'content' => ['nullable', 'string'],
        ];
    }
}
