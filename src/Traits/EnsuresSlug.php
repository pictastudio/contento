<?php

namespace PictaStudio\Contento\Traits;

use Illuminate\Database\Eloquent\Model;

trait EnsuresSlug
{
    protected static function bootEnsuresSlug(): void
    {
        static::saving(function (Model $model) {
            if (method_exists($model, 'generateSlug')) {
                $model->generateSlug();
            }
        });
    }
}
