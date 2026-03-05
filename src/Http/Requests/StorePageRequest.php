<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PictaStudio\Contento\Http\Requests\Concerns\InteractsWithTranslatableInput;
use PictaStudio\Contento\Validations\Contracts\PageValidationRules;

class StorePageRequest extends FormRequest
{
    use InteractsWithTranslatableInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(PageValidationRules $validationRules): array
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
        $this->prepareTranslatableInput(['title', 'abstract', 'slug']);

        if (!$this->isMethod('post')) {
            return;
        }

        if (!$this->has('abstract')) {
            $this->merge(['abstract' => '']);
        }
    }
}
