<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PictaStudio\Contento\Http\Requests\Concerns\InteractsWithTranslatableInput;
use PictaStudio\Contento\Validations\Contracts\ContentTagValidationRules;

class StoreContentTagRequest extends FormRequest
{
    use InteractsWithTranslatableInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(ContentTagValidationRules $validationRules): array
    {
        return $validationRules->getStoreValidationRules();
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->filled('name') || $this->hasTranslatableValue('name')) {
                return;
            }

            $validator->errors()->add('name', 'The name field is required.');
        });
    }

    protected function prepareForValidation(): void
    {
        $this->prepareTranslatableInput(['name', 'slug', 'abstract', 'description']);
    }
}
