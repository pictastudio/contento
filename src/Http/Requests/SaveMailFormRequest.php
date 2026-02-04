<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveMailFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
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
