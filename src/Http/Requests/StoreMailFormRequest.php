<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PictaStudio\Contento\Http\Requests\Concerns\InteractsWithTranslatableInput;
use PictaStudio\Contento\Validations\Contracts\MailFormValidationRules;

class StoreMailFormRequest extends FormRequest
{
    use InteractsWithTranslatableInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(MailFormValidationRules $validationRules): array
    {
        return $this->isMethod('post')
            ? $validationRules->getStoreValidationRules()
            : $validationRules->getUpdateValidationRules();
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (!$this->isMethod('post')) {
                return;
            }

            if ($this->filled('name') || $this->hasTranslatableValue('name')) {
                return;
            }

            $validator->errors()->add('name', 'The name field is required.');
        });
    }

    protected function prepareForValidation(): void
    {
        $this->prepareTranslatableInput(['name', 'slug', 'custom_fields', 'redirect_url']);
    }
}
