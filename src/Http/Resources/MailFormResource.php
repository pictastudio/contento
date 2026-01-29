<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use PictaStudio\Contento\Http\Resources\Traits\CanTransformAttributes;

class MailFormResource extends JsonResource
{
    use CanTransformAttributes;

    public function toArray(Request $request): array
    {
        return $this->applyAttributesTransformation(
            collect(parent::toArray($request))
                ->map(fn (mixed $value, string $key) => (
                    $this->mutateAttributeBasedOnCast($key, $value)
                ))
                ->merge($this->getRelationshipsToInclude())
                ->toArray()
        );
    }

    protected function getRelationshipsToInclude(): array
    {
        return [
            //
        ];
    }

    protected function transformAttributes(): array
    {
        return [
            //
        ];
    }
}
