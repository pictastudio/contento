<?php

namespace PictaStudio\Contento\Actions\Galleries;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UpdateGallery
{
    public function handle(Model $gallery, array $payload): Model
    {
        return DB::transaction(function () use ($gallery, $payload): Model {
            $gallery->update($payload);

            return $gallery->refresh();
        });
    }
}
