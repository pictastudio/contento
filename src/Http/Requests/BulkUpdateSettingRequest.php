<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use PictaStudio\Contento\Validations\Contracts\SettingValidationRules;

class BulkUpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(SettingValidationRules $validationRules): array
    {
        return $validationRules->getBulkUpdateValidationRules();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ((array) $this->input('settings', []) as $index => $setting) {
                if (!is_array($setting)) {
                    continue;
                }

                $hasId = array_key_exists('id', $setting) && $setting['id'] !== null;
                if ($hasId) {
                    continue;
                }

                if (!array_key_exists('group', $setting) || $setting['group'] === null || $setting['group'] === '') {
                    $validator->errors()->add(
                        "settings.{$index}.group",
                        'The group field is required when id is not present.'
                    );
                }

                if (!array_key_exists('name', $setting) || $setting['name'] === null || $setting['name'] === '') {
                    $validator->errors()->add(
                        "settings.{$index}.name",
                        'The name field is required when id is not present.'
                    );
                }
            }
        });
    }
}
