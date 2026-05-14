<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PictaStudio\Contento\Http\Requests\Concerns\InteractsWithTranslatableInput;
use PictaStudio\Contento\Validations\Contracts\GalleryItemValidationRules;

class UpdateGalleryItemRequest extends FormRequest
{
    use InteractsWithTranslatableInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(GalleryItemValidationRules $validationRules): array
    {
        return $validationRules->getUpdateValidationRules();
    }

    protected function prepareForValidation(): void
    {
        $this->prepareTranslatableInput(['title', 'subtitle', 'description']);
        $this->normalizeImgMetadataInput();
    }

    private function normalizeImgMetadataInput(): void
    {
        if (!$this->has('img.metadata') || !is_array($this->input('img.metadata'))) {
            return;
        }

        $img = $this->input('img', []);
        $img['metadata'] = $this->nullEmptyStrings($this->input('img.metadata'));

        $this->merge(['img' => $img]);
    }

    private function nullEmptyStrings(array $values): array
    {
        return array_map(function (mixed $value): mixed {
            if (is_array($value)) {
                return $this->nullEmptyStrings($value);
            }

            return $value === '' ? null : $value;
        }, $values);
    }
}
