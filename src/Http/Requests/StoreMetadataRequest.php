<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use PictaStudio\Contento\Validations\Contracts\MetadataValidationRules;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class StoreMetadataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(MetadataValidationRules $validationRules): array
    {
        if ($this->isMethod('post')) {
            return $validationRules->getStoreValidationRules();
        }

        return $this->withUniqueUpdateRules(
            $validationRules->getUpdateValidationRules()
        );
    }

    private function withUniqueUpdateRules(array $rules): array
    {
        $table = (new (resolve_model('metadata')))->getTable();
        $metadata = $this->route('metadata');
        $ignoreId = $metadata instanceof Model
            ? $metadata->getKey()
            : null;

        if (array_key_exists('slug', $rules)) {
            $rules['slug'][] = Rule::unique($table, 'slug')->ignore($ignoreId);
        }

        if (array_key_exists('uri', $rules)) {
            $rules['uri'][] = Rule::unique($table, 'uri')->ignore($ignoreId);
        }

        return $rules;
    }
}
