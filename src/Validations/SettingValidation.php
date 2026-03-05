<?php

namespace PictaStudio\Contento\Validations;

use Illuminate\Validation\Rule;
use PictaStudio\Contento\Validations\Contracts\SettingValidationRules;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class SettingValidation implements SettingValidationRules
{
    public function getStoreValidationRules(): array
    {
        return [
            'group' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'string'],
        ];
    }

    public function getUpdateValidationRules(): array
    {
        return [
            'group' => ['sometimes', 'string', 'max:255'],
            'name' => ['sometimes', 'string', 'max:255'],
            'value' => ['nullable', 'string'],
        ];
    }

    public function getBulkUpdateValidationRules(): array
    {
        return [
            'settings' => ['required', 'array', 'min:1'],
            'settings.*' => ['required', 'array'],
            'settings.*.id' => ['nullable', 'integer', Rule::exists($this->tableFor('setting'), 'id')],
            'settings.*.group' => ['nullable', 'string', 'max:255'],
            'settings.*.name' => ['nullable', 'string', 'max:255'],
            'settings.*.value' => ['present', 'nullable', 'string'],
        ];
    }

    private function tableFor(string $model): string
    {
        return (new (resolve_model($model)))->getTable();
    }
}
