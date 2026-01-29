<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'active' => $this->active,
            'template' => $this->template,
            'content' => $this->content,
            'cta_button_text' => $this->cta_button_text,
            'cta_button_url' => $this->cta_button_url,
            'cta_button_color' => $this->cta_button_color,
            'image' => $this->image,
            'timeout' => $this->timeout,
            'popup_time' => $this->popup_time,
            'show_on_all_pages' => $this->show_on_all_pages,
            'visible_date_from' => $this->visible_date_from,
            'visible_date_to' => $this->visible_date_to,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
