<?php

namespace PictaStudio\Contento\Actions\Galleries;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateGallery
{
    public function handle(Model $gallery, array $payload): Model
    {
        return DB::transaction(function () use ($gallery, $payload): Model {
            $galleryItemsProvided = array_key_exists('gallery_items', $payload);
            $galleryItems = Arr::pull($payload, 'gallery_items', []);

            $gallery->update($payload);

            if ($galleryItemsProvided) {
                app(UpsertGalleryItemsForGallery::class)->handle($gallery, $galleryItems);

                return $gallery->refresh()->load('items');
            }

            return $gallery->refresh();
        });
    }
}
