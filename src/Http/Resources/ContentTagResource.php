<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use PictaStudio\Contento\Http\Resources\Traits\CanTransformAttributes;

class ContentTagResource extends JsonResource
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
            'parent' => self::make($this->whenLoaded('parent')),
            'children' => self::collection($this->whenLoaded('children')),
            'content_tags' => self::collection($this->whenLoaded('contentTags')),
            'pages' => PageResource::collection($this->whenLoaded('pages')),
            'faq_categories' => FaqCategoryResource::collection($this->whenLoaded('faqCategories')),
            'faqs' => FaqResource::collection($this->whenLoaded('faqs')),
        ];
    }

    protected function transformAttributes(): array
    {
        return [
            //
        ];
    }
}
