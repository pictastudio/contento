<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PictaStudio\Contento\Validations\Contracts\SettingValidationRules;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(SettingValidationRules $validationRules): array
    {
        return $validationRules->getStoreValidationRules();
    }
}
