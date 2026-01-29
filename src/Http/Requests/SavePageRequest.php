<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use PictaStudio\Contento\Models\Page;

class SavePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tableName = (string) config('contento.table_names.pages');
        $page = $this->route('page');
        $id = $page instanceof Page ? $page->getKey() : $page;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique($tableName, 'slug')->ignore($id),
            ],
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
