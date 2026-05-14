<?php

namespace PictaStudio\Contento\Actions\GalleryItems;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use PictaStudio\Contento\Support\GalleryItemImage;

class UpdateGalleryItem
{
    public function handle(Model $galleryItem, array $payload): Model
    {
        return DB::transaction(function () use ($galleryItem, $payload): Model {
            $imgProvided = array_key_exists('img', $payload);
            $img = Arr::pull($payload, 'img');

            if ($imgProvided) {
                GalleryItemImage::validatePayload($img, $galleryItem->getAttribute('img'));
                $payload['img'] = GalleryItemImage::merge($galleryItem, $galleryItem->getAttribute('img'), $img);
            }

            $galleryItem->fill($payload);
            $galleryItem->save();

            return $galleryItem->refresh();
        });
    }
}
