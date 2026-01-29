<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveMailFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tableName = config('contento.table_names.mail_forms');
        $id = $this->route('mail_form');

        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:' . $tableName . ',slug,' . $id,
            'email_to' => 'nullable|email',
            'email_cc' => 'nullable|string', // Could be comma separated
            'email_bcc' => 'nullable|string',
            'custom_fields' => 'nullable|array',
            'redirect_url' => 'nullable|url',
            'custom_data' => 'nullable|string',
            'options' => 'nullable|string',
            'newsletter' => 'boolean',
        ];
    }
}
