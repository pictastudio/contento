<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use PictaStudio\Contento\Models\FaqCategory;

class SaveFaqCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tableName = (string) config('contento.table_names.faq_categories');
        $faqCategory = $this->route('faq_category');
        $id = $faqCategory instanceof FaqCategory ? $faqCategory->getKey() : $faqCategory;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique($tableName, 'slug')->ignore($id),
            ],
            'active' => ['boolean'],
            'abstract' => ['nullable', 'string'],
        ];
    }
}
