<?php

namespace PictaStudio\Contento\Validations;

use Illuminate\Validation\Rule;
use PictaStudio\Contento\Validations\Concerns\InteractsWithTranslatableRules;
use PictaStudio\Contento\Validations\Contracts\GalleryItemValidationRules;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class GalleryItemValidation implements GalleryItemValidationRules
{
    use InteractsWithTranslatableRules;

    public function getStoreValidationRules(): array
    {
        return [
            'gallery_id' => ['required', 'integer', Rule::exists($this->tableFor('gallery'), 'id')],
            'title' => ['sometimes', 'filled', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'active' => ['sometimes', 'boolean'],
            'visible_from' => ['nullable', 'date'],
            'visible_until' => ['nullable', 'date', 'after_or_equal:visible_from'],
            'links' => ['sometimes', 'nullable', 'array'],
            'img' => ['sometimes', 'nullable', 'array'],
            'img.id' => ['nullable', 'string', 'max:255'],
            'img.file' => $this->imageFileRules(),
            'img.alt' => ['nullable', 'string', 'max:255'],
            'img.name' => ['nullable', 'string', 'max:255'],
            'img.mimetype' => ['nullable', 'string', 'max:255'],
            'img.metadata' => ['nullable', 'array'],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'filled', 'string', 'max:255'],
                'subtitle' => ['sometimes', 'nullable', 'string', 'max:255'],
                'description' => ['sometimes', 'nullable', 'string'],
            ], ['title', 'subtitle', 'description']),
        ];
    }

    public function getUpdateValidationRules(): array
    {
        return [
            'gallery_id' => ['sometimes', 'integer', Rule::exists($this->tableFor('gallery'), 'id')],
            'title' => ['sometimes', 'filled', 'string', 'max:255'],
            'subtitle' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'active' => ['sometimes', 'boolean'],
            'visible_from' => ['nullable', 'date'],
            'visible_until' => ['nullable', 'date', 'after_or_equal:visible_from'],
            'links' => ['sometimes', 'nullable', 'array'],
            'img' => ['sometimes', 'nullable', 'array'],
            'img.id' => ['nullable', 'string', 'max:255'],
            'img.file' => $this->imageFileRules(),
            'img.alt' => ['nullable', 'string', 'max:255'],
            'img.name' => ['nullable', 'string', 'max:255'],
            'img.mimetype' => ['nullable', 'string', 'max:255'],
            'img.metadata' => ['nullable', 'array'],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'filled', 'string', 'max:255'],
                'subtitle' => ['sometimes', 'nullable', 'string', 'max:255'],
                'description' => ['sometimes', 'nullable', 'string'],
            ], ['title', 'subtitle', 'description']),
        ];
    }

    private function imageFileRules(): array
    {
        $rules = ['sometimes', 'file', 'image'];
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

    private function tableFor(string $model): string
    {
        return (new (resolve_model($model)))->getTable();
    }
}
