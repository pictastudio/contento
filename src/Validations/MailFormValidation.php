<?php

namespace PictaStudio\Contento\Validations;

use PictaStudio\Contento\Validations\Contracts\MailFormValidationRules;

class MailFormValidation implements MailFormValidationRules
{
    public function getStoreValidationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email_to' => ['nullable', 'email'],
            'email_cc' => ['nullable', 'string'],
            'email_bcc' => ['nullable', 'string'],
            'custom_fields' => ['nullable', 'array'],
            'redirect_url' => ['nullable', 'url'],
            'custom_data' => ['nullable', 'string'],
            'options' => ['nullable', 'string'],
            'newsletter' => ['boolean'],
            'tag_ids' => ['prohibited'],
        ];
    }

    public function getUpdateValidationRules(): array
    {
        return $this->getStoreValidationRules();
    }
}
