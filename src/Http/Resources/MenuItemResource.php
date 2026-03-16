<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use PictaStudio\Contento\Http\Resources\Traits\CanTransformAttributes;

class MenuItemResource extends JsonResource
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
            'menu' => MenuResource::make($this->whenLoaded('menu')),
            'parent' => self::make($this->whenLoaded('parent')),
            'children' => self::collection($this->whenLoaded('children')),
        ];
    }

    protected function transformAttributes(): array
    {
        return [
            //
        ];
    }
}
