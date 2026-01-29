<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveModalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tableName = config('contento.table_names.modals');
        $id = $this->route('modal');

        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:' . $tableName . ',slug,' . $id,
            'active' => 'boolean',
            'visible_date_from' => 'nullable|date',
            'visible_date_to' => 'nullable|date',
            'template' => 'nullable|string',
            'content' => 'nullable|string',
            'cta_button_text' => 'nullable|string',
            'cta_button_url' => 'nullable|string',
            'cta_button_color' => 'nullable|string',
            'image' => 'nullable|string',
            'timeout' => 'integer',
            'popup_time' => 'nullable|string',
            'show_on_all_pages' => 'boolean',
        ];
    }
}
