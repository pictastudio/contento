<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CatalogImageResource extends ContentoJsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'alt' => $this->alt,
            'caption' => $this->caption,
            'disk' => $this->disk,
            'path' => $this->path,
            'url' => $this->url(),
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'width' => $this->width,
            'height' => $this->height,
            'metadata' => $this->metadata,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function url(): ?string
    {
        if (blank($this->path) || blank($this->disk)) {
            return null;
        }

        return Storage::disk((string) $this->disk)->url((string) $this->path);
    }
}
