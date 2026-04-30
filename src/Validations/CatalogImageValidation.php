<?php

namespace PictaStudio\Contento\Validations;

use PictaStudio\Contento\Validations\Contracts\CatalogImageValidationRules;

class CatalogImageValidation implements CatalogImageValidationRules
{
    public function getStoreValidationRules(): array
    {
        return [
            'file' => $this->fileRules(required: true),
            'name' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'alt' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function getUpdateValidationRules(): array
    {
        return [
            'file' => $this->fileRules(required: false),
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'alt' => ['sometimes', 'nullable', 'string', 'max:255'],
            'caption' => ['sometimes', 'nullable', 'string'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    private function fileRules(bool $required): array
    {
        $rules = [$required ? 'required' : 'sometimes', 'file', 'image'];
        $mimetypes = config('contento.catalog_images.allowed_mimetypes', []);

        if (is_array($mimetypes) && $mimetypes !== []) {
            $rules[] = 'mimetypes:' . implode(',', array_filter($mimetypes, 'is_string'));
        }

        $maxUploadKilobytes = (int) config('contento.catalog_images.max_upload_kilobytes', 5120);

        if ($maxUploadKilobytes > 0) {
            $rules[] = 'max:' . $maxUploadKilobytes;
        }

        return $rules;
    }
}
