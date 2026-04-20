<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;

class MenuResource extends ContentoJsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'active' => $this->active,
            'visible_date_from' => $this->visible_date_from,
            'visible_date_to' => $this->visible_date_to,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items' => MenuItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
