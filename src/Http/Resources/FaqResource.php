<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;

class FaqResource extends ContentoJsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'faq_category_id' => $this->faq_category_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'active' => $this->active,
            'sort_order' => $this->sort_order,
            'visible_date_from' => $this->visible_date_from,
            'visible_date_to' => $this->visible_date_to,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'category' => FaqCategoryResource::make($this->whenLoaded('category')),
        ];
    }
}
