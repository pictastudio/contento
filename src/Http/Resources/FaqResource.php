<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'faq_category_id' => $this->faq_category_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'active' => $this->active,
            'content' => $this->content,
            'visible_date_from' => $this->visible_date_from,
            'visible_date_to' => $this->visible_date_to,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
