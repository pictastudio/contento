<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;

class GalleryItemResource extends ContentoJsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'gallery_id' => $this->gallery_id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'active' => $this->active,
            'visible_from' => $this->visible_from,
            'visible_until' => $this->visible_until,
            'links' => $this->links,
            'img' => $this->transformGalleryItemImage($this->img),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'gallery' => GalleryResource::make($this->whenLoaded('gallery')),
        ];
    }
}
