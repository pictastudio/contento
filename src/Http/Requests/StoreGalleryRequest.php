<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PictaStudio\Contento\Http\Requests\Concerns\InteractsWithTranslatableInput;
use PictaStudio\Contento\Validations\Contracts\GalleryValidationRules;

class StoreGalleryRequest extends FormRequest
{
    use InteractsWithTranslatableInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(GalleryValidationRules $validationRules): array
    {
        return $validationRules->getStoreValidationRules();
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->filled('title') || $this->hasTranslatableValue('title')) {
                return;
            }

            $validator->errors()->add('title', 'The title field is required.');
        });
    }

    protected function prepareForValidation(): void
    {
        $this->prepareTranslatableInput(['title', 'slug', 'abstract']);
    }
}
