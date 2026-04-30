<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PictaStudio\Contento\Validations\Contracts\CatalogImageValidationRules;

class StoreCatalogImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(CatalogImageValidationRules $validationRules): array
    {
        return $validationRules->getStoreValidationRules();
    }
}
