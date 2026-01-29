<?php

namespace PictaStudio\Contento\Http\Resources\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait CanTransformAttributes
{
    abstract protected function transformAttributes(): array;

    private function applyAttributesTransformation(array $attributes): array
    {
        $transformedAttributes = $this->transformAttributes();

        if (empty($transformedAttributes)) {
            return $attributes;
        }

        foreach ($transformedAttributes as $key => $closure) {
            Arr::set($attributes, $key, $closure(Arr::get($attributes, $key)));
        }

        return $attributes;
    }

    private function mutateAttributeBasedOnCast(string $key, mixed $value): mixed
    {
        /** @var Model $model */
        $model = $this->resource;

        if (!$model->hasCast($key)) {
            return $value;
        }

        $cast = $model->getCasts()[$key];

        if (str_contains($cast, 'decimal')) {
            return (float) $value;
        }

        if (in_array($cast, ['int', 'integer'])) {
            return (int) $value;
        }

        if (in_array($cast, ['bool', 'boolean'])) {
            return (bool) $value;
        }

        return $value;
    }
}
