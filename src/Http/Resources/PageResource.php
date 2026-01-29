<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type,
            'active' => $this->active,
            'important' => $this->important,
            'visible_date_from' => $this->visible_date_from,
            'visible_date_to' => $this->visible_date_to,
            'published_at' => $this->published_at,
            'author' => $this->author,
            'abstract' => $this->abstract,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
