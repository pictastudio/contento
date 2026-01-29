<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'group' => $this->group,
            'name' => $this->name,
            'value' => $this->value,
        ];
    }
}
