<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveFaqCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tableName = config('contento.table_names.faq_categories');
        $id = $this->route('faq_category');

        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:' . $tableName . ',slug,' . $id,
            'active' => 'boolean',
            'abstract' => 'nullable|string',
        ];
    }
}
