<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PictaStudio\Contento\Validations\Contracts\MailFormValidationRules;

class StoreMailFormRequest extends FormRequest
{
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
}
