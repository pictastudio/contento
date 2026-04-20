<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;

class FaqCategoryResource extends ContentoJsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'abstract' => $this->abstract,
            'active' => $this->active,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'faqs' => FaqResource::collection($this->whenLoaded('faqs')),
        ];
    }
}
