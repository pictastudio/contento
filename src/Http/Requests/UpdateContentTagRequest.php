<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PictaStudio\Contento\Http\Requests\Concerns\{InteractsWithTranslatableInput, NormalizesMetadataInput};
use PictaStudio\Contento\Validations\Contracts\ContentTagValidationRules;

class UpdateContentTagRequest extends FormRequest
{
    use InteractsWithTranslatableInput;
    use NormalizesMetadataInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(ContentTagValidationRules $validationRules): array
    {
        return $validationRules->getUpdateValidationRules();
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeMetadataInput();
        $this->prepareTranslatableInput(['name', 'slug', 'abstract', 'description']);
    }
}
