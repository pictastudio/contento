<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;

class PageResource extends ContentoJsonResource
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
            'metadata' => $this->metadata,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'content_tags' => ContentTagResource::collection($this->whenLoaded('contentTags')),
        ];
    }
}
