<?php

namespace PictaStudio\Contento\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

trait HasAuthors
{
    protected static function bootHasAuthors(): void
    {
        static::creating(function (Model $model) {
            $authorId = auth()->guard()->id();

            if ($authorId !== null) {
                $model->created_by = $authorId;
            }
        });

        static::updating(function (Model $model) {
            $authorId = auth()->guard()->id();

            if ($authorId !== null) {
                $model->updated_by = $authorId;
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(resolve_model('user'), 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(resolve_model('user'), 'updated_by');
    }
}
