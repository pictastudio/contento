<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PictaStudio\Contento\Http\Requests\Concerns\NormalizesMetadataInput;
use PictaStudio\Contento\Validations\Contracts\CatalogImageValidationRules;

class StoreCatalogImageRequest extends FormRequest
{
    use NormalizesMetadataInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(CatalogImageValidationRules $validationRules): array
    {
        return $validationRules->getStoreValidationRules();
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeMetadataInput();
    }
}
