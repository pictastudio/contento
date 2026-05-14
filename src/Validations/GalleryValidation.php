<?php

namespace PictaStudio\Contento\Validations;

use Illuminate\Validation\Rule;
use PictaStudio\Contento\Validations\Concerns\InteractsWithTranslatableRules;
use PictaStudio\Contento\Validations\Contracts\GalleryValidationRules;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class GalleryValidation implements GalleryValidationRules
{
    use InteractsWithTranslatableRules;

    public function getStoreValidationRules(): array
    {
        return [
            'title' => ['sometimes', 'filled', 'string', 'max:255'],
            'slug' => ['sometimes', 'filled', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique($this->tableFor('gallery'), 'code')],
            'abstract' => ['nullable', 'string'],
            'active' => ['sometimes', 'boolean'],
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'filled', 'string', 'max:255'],
                'slug' => ['sometimes', 'filled', 'string', 'max:255'],
                'abstract' => ['sometimes', 'nullable', 'string'],
            ], ['title', 'slug', 'abstract']),
        ];
    }

    public function getUpdateValidationRules(): array
    {
        return [
            'title' => ['sometimes', 'filled', 'string', 'max:255'],
            'slug' => ['sometimes', 'filled', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:255', Rule::unique($this->tableFor('gallery'), 'code')->ignore(request()->route('gallery'))],
            'abstract' => ['sometimes', 'nullable', 'string'],
            'active' => ['sometimes', 'boolean'],
            'gallery_items' => ['sometimes', 'array'],
            'gallery_items.*' => ['array'],
            'gallery_items.*.id' => ['sometimes', 'integer'],
            'gallery_items.*.gallery_id' => ['sometimes', 'integer', Rule::exists($this->tableFor('gallery'), 'id')],
            'gallery_items.*.title' => ['sometimes', 'filled', 'string', 'max:255'],
            'gallery_items.*.subtitle' => ['sometimes', 'nullable', 'string', 'max:255'],
            'gallery_items.*.description' => ['sometimes', 'nullable', 'string'],
            'gallery_items.*.sort_order' => ['sometimes', 'integer', 'min:0'],
            'gallery_items.*.active' => ['sometimes', 'boolean'],
            'gallery_items.*.visible_from' => ['nullable', 'date'],
            'gallery_items.*.visible_until' => ['nullable', 'date', 'after_or_equal:gallery_items.*.visible_from'],
            'gallery_items.*.links' => ['sometimes', 'nullable', 'array'],
            'gallery_items.*.img' => ['sometimes', 'nullable', 'array'],
            'gallery_items.*.img.id' => ['nullable', 'string', 'max:255'],
            'gallery_items.*.img.file' => $this->imageFileRules(),
            'gallery_items.*.img.alt' => ['nullable', 'string', 'max:255'],
            'gallery_items.*.img.name' => ['nullable', 'string', 'max:255'],
            'gallery_items.*.img.mimetype' => ['nullable', 'string', 'max:255'],
            'gallery_items.*.img.metadata' => ['nullable', 'array'],
            ...$this->nestedTranslatableLocaleRules('gallery_items', [
                'title' => ['sometimes', 'filled', 'string', 'max:255'],
                'subtitle' => ['sometimes', 'nullable', 'string', 'max:255'],
                'description' => ['sometimes', 'nullable', 'string'],
            ], ['title', 'subtitle', 'description']),
            ...$this->translatableLocaleRules([
                'title' => ['sometimes', 'filled', 'string', 'max:255'],
                'slug' => ['sometimes', 'filled', 'string', 'max:255'],
                'abstract' => ['sometimes', 'nullable', 'string'],
            ], ['title', 'slug', 'abstract']),
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

    private function nestedTranslatableLocaleRules(
        string $attribute,
        array $attributesRules,
        array $allowedLocalizedAttributes
    ): array {
        $rules = [];

        foreach ($this->translatableLocales() as $locale) {
            $rules["{$attribute}.*.{$locale}"] = ['sometimes', 'array:' . implode(',', $allowedLocalizedAttributes)];

            foreach ($attributesRules as $localizedAttribute => $localizedAttributeRules) {
                $rules["{$attribute}.*.{$locale}.{$localizedAttribute}"] = $localizedAttributeRules;
                $rules["{$attribute}.*.{$localizedAttribute}:{$locale}"] = $localizedAttributeRules;
            }
        }

        return $rules;
    }

    private function tableFor(string $model): string
    {
        return (new (resolve_model($model)))->getTable();
    }
}
