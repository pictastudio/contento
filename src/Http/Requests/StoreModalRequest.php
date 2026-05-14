<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PictaStudio\Contento\Http\Requests\Concerns\InteractsWithTranslatableInput;
use PictaStudio\Contento\Validations\Contracts\ModalValidationRules;

class StoreModalRequest extends FormRequest
{
    use InteractsWithTranslatableInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(ModalValidationRules $validationRules): array
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

            if ($this->filled('title') || $this->hasTranslatableValue('title')) {
                return;
            }

            $validator->errors()->add('title', 'The title field is required.');
        });
    }

    protected function prepareForValidation(): void
    {
        $this->prepareTranslatableInput(['title', 'content', 'cta_button_text', 'cta_button_url', 'slug']);

        if (!$this->isMethod('post')) {
            return;
        }

        $merge = [];

        if (!$this->filled('cta_button_text')) {
            $merge['cta_button_text'] = '';
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }
}
