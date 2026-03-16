<?php

namespace PictaStudio\Contento\Validations;

use PictaStudio\Contento\Validations\Concerns\InteractsWithTranslatableRules;
use PictaStudio\Contento\Validations\Contracts\MailFormValidationRules;

class MailFormValidation implements MailFormValidationRules
{
    use InteractsWithTranslatableRules;

    public function getStoreValidationRules(): array
    {
        return [
            'name' => ['sometimes', 'filled', 'string', 'max:255'],
            'slug' => ['sometimes', 'filled', 'string', 'max:255'],
            'email_to' => ['nullable', 'email'],
            'email_cc' => ['nullable', 'string'],
            'email_bcc' => ['nullable', 'string'],
            'custom_fields' => ['nullable', 'array'],
            'redirect_url' => ['nullable', 'url'],
            'custom_data' => ['nullable', 'string'],
            'options' => ['nullable', 'string'],
            'newsletter' => ['boolean'],
            'tag_ids' => ['prohibited'],
            ...$this->translatableLocaleRules([
                'name' => ['sometimes', 'filled', 'string', 'max:255'],
                'slug' => ['sometimes', 'filled', 'string', 'max:255'],
                'custom_fields' => ['sometimes', 'nullable', 'array'],
                'redirect_url' => ['sometimes', 'nullable', 'url'],
            ], ['name', 'slug', 'custom_fields', 'redirect_url']),
        ];
    }

    public function getUpdateValidationRules(): array
    {
        return $this->getStoreValidationRules();
    }
}
