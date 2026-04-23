<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;

class ContentTagResource extends ContentoJsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'path' => $this->path,
            'name' => $this->name,
            'slug' => $this->slug,
            'abstract' => $this->abstract,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'images' => $this->transformCatalogImageCollection($this->images),
            'active' => $this->active,
            'show_in_menu' => $this->show_in_menu,
            'in_evidence' => $this->in_evidence,
            'sort_order' => $this->sort_order,
            'visible_from' => $this->visible_from,
            'visible_until' => $this->visible_until,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'parent' => self::make($this->whenLoaded('parent')),
            'children' => self::collection($this->whenLoaded('children')),
            'content_tags' => self::collection($this->whenLoaded('contentTags')),
            'pages' => PageResource::collection($this->whenLoaded('pages')),
            'faq_categories' => FaqCategoryResource::collection($this->whenLoaded('faqCategories')),
            'faqs' => FaqResource::collection($this->whenLoaded('faqs')),
        ];
    }
}
