<?php

namespace PictaStudio\Contento\Traits;

trait HasAuthors
{
    public function creator()
    {
        return $this->belongsTo(config('contento.user_model', 'App\\Models\\User'), 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(config('contento.user_model', 'App\\Models\\User'), 'updated_by');
    }
}
