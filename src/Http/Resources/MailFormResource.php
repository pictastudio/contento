<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Request;

class MailFormResource extends ContentoJsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'email_to' => $this->email_to,
            'email_cc' => $this->email_cc,
            'email_bcc' => $this->email_bcc,
            'custom_fields' => $this->custom_fields,
            'redirect_url' => $this->redirect_url,
            'custom_data' => $this->custom_data,
            'options' => $this->options,
            'newsletter' => $this->newsletter,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
