<?php

namespace PictaStudio\Contento\Actions\GalleryItems;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use PictaStudio\Contento\Support\GalleryItemImage;

use function PictaStudio\Contento\Helpers\Functions\query;

class CreateGalleryItem
{
    public function handle(array $payload): Model
    {
        return DB::transaction(function () use ($payload): Model {
            $imgProvided = array_key_exists('img', $payload);
            $img = Arr::pull($payload, 'img');

            if ($imgProvided) {
                GalleryItemImage::validatePayload($img);
            }

            $galleryItem = query('gallery_item')->create($payload);

            if ($imgProvided) {
                $galleryItem->img = GalleryItemImage::merge($galleryItem, null, $img);
                $galleryItem->save();
            }

            return $galleryItem->refresh();
        });
    }
}
