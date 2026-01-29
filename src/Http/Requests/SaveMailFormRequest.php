<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use PictaStudio\Contento\Models\MailForm;

class SaveMailFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tableName = (string) config('contento.table_names.mail_forms');
        $mailForm = $this->route('mail_form');
        $id = $mailForm instanceof MailForm ? $mailForm->getKey() : $mailForm;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique($tableName, 'slug')->ignore($id),
            ],
            'email_to' => ['nullable', 'email'],
            'email_cc' => ['nullable', 'string'],
            'email_bcc' => ['nullable', 'string'],
            'custom_fields' => ['nullable', 'array'],
            'redirect_url' => ['nullable', 'url'],
            'custom_data' => ['nullable', 'string'],
            'options' => ['nullable', 'string'],
            'newsletter' => ['boolean'],
        ];
    }
}
