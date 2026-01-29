<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tableName = config('contento.table_names.faqs');
        $id = $this->route('faq');

        return [
            'faq_category_id' => 'required|exists:' . config('contento.table_names.faq_categories') . ',id',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:' . $tableName . ',slug,' . $id,
            'active' => 'boolean',
            'visible_date_from' => 'nullable|date',
            'visible_date_to' => 'nullable|date',
            'content' => 'nullable|string',
        ];
    }
}
