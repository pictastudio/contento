<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use PictaStudio\Contento\Models\Modal;

class SaveModalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tableName = (string) config('contento.table_names.modals');
        $modal = $this->route('modal');
        $id = $modal instanceof Modal ? $modal->getKey() : $modal;

        return [
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
