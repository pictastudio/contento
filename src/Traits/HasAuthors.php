<?php

namespace PictaStudio\Contento\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User;

trait HasAuthors
{
    protected static function bootHasAuthors()
    {
        static::creating(function ($model) {
            $model->created_by = auth()->guard()->id();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->guard()->id();
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
