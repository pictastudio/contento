<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\{Rule, Validator};

abstract class IndexQueryRequest extends FormRequest
{
    abstract protected function filterRules(): array;

    abstract protected function sortableFields(): array;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            $this->paginationAndSortingRules(),
            $this->filterRules()
        );
    }

    public function validationData(): array
    {
        return $this->query();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowedQueryParameters = $this->allowedQueryParameters();
            $providedQueryParameters = array_keys($this->query());

            foreach (array_diff($providedQueryParameters, $allowedQueryParameters) as $parameter) {
                $validator->errors()->add($parameter, sprintf('The [%s] query parameter is not supported.', $parameter));
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach ($this->queryAliases() as $alias => $target) {
            if (!$this->has($target) && $this->has($alias)) {
                $normalized[$target] = $this->query($alias);
            }
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    protected function allowedQueryParameters(): array
    {
        $ruleKeys = array_filter(
            array_keys($this->rules()),
            fn (string $key): bool => !str_contains($key, '.')
        );

        return array_values(array_unique(array_merge($ruleKeys, array_keys($this->queryAliases()))));
    }

    protected function paginationAndSortingRules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:' . $this->maxPerPage()],
            'sort_by' => ['sometimes', 'string', Rule::in($this->sortableFields())],
            'sort_dir' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    protected function maxPerPage(): int
    {
        return max(1, (int) config('contento.query.max_per_page', 100));
    }

    protected function queryAliases(): array
    {
        return [];
    }
}
