<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MailFormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'custom_fields' => $this->custom_fields,
            'redirect_url' => $this->redirect_url,
            'custom_data' => $this->custom_data,
            'options' => $this->options,
            'newsletter' => $this->newsletter,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
