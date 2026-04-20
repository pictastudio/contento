<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;

class ModalResource extends ContentoJsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'cta_button_text' => $this->cta_button_text,
            'cta_button_url' => $this->cta_button_url,
            'cta_button_color' => $this->cta_button_color,
            'image' => $this->image,
            'template' => $this->template,
            'timeout' => $this->timeout,
            'popup_time' => $this->popup_time,
            'show_on_all_pages' => $this->show_on_all_pages,
            'active' => $this->active,
            'visible_date_from' => $this->visible_date_from,
            'visible_date_to' => $this->visible_date_to,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
