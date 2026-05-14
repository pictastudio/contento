<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;

class GalleryResource extends ContentoJsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'code' => $this->code,
            'abstract' => $this->abstract,
            'active' => $this->active,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items' => GalleryItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
