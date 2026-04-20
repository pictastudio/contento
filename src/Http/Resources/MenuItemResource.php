<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;

class MenuItemResource extends ContentoJsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'menu_id' => $this->menu_id,
            'parent_id' => $this->parent_id,
            'path' => $this->path === null ? null : (string) $this->path,
            'title' => $this->title,
            'slug' => $this->slug,
            'link' => $this->link,
            'active' => $this->active,
            'sort_order' => $this->sort_order,
            'visible_date_from' => $this->visible_date_from,
            'visible_date_to' => $this->visible_date_to,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'menu' => MenuResource::make($this->whenLoaded('menu')),
            'parent' => self::make($this->whenLoaded('parent')),
            'children' => self::collection($this->whenLoaded('children')),
        ];
    }
}
