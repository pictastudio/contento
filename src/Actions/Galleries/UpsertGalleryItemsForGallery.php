<?php

namespace PictaStudio\Contento\Actions\Galleries;

use Illuminate\Database\Eloquent\{Collection, Model};
use Illuminate\Support\Arr;
use PictaStudio\Contento\Actions\GalleryItems\{CreateGalleryItem, UpdateGalleryItem};

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class UpsertGalleryItemsForGallery
{
    public function handle(Model $gallery, array $galleryItems): Collection
    {
        $upsertedGalleryItemIds = [];
        $galleryItemModelClass = resolve_model('gallery_item');
        $createGalleryItem = app(CreateGalleryItem::class);
        $updateGalleryItem = app(UpdateGalleryItem::class);

        foreach ($galleryItems as $galleryItemPayload) {
            if (!is_array($galleryItemPayload)) {
                continue;
            }

            $galleryItemId = array_key_exists('id', $galleryItemPayload) && filled($galleryItemPayload['id'])
                ? (int) $galleryItemPayload['id']
                : null;

            if ($galleryItemId !== null) {
                $galleryItem = $galleryItemModelClass::query()
                    ->withoutGlobalScopes()
                    ->where('gallery_id', $gallery->getKey())
                    ->whereKey($galleryItemId)
                    ->firstOrFail();

                $upsertedGalleryItem = $updateGalleryItem->handle(
                    $galleryItem,
                    Arr::except($galleryItemPayload, ['id', 'gallery_id'])
                );
                $upsertedGalleryItemIds[] = (int) $upsertedGalleryItem->getKey();

                continue;
            }

            $upsertedGalleryItem = $createGalleryItem->handle([
                ...Arr::except($galleryItemPayload, ['gallery_id']),
                'gallery_id' => $gallery->getKey(),
            ]);
            $upsertedGalleryItemIds[] = (int) $upsertedGalleryItem->getKey();
        }

        return $galleryItemModelClass::query()
            ->withoutGlobalScopes()
            ->whereKey($upsertedGalleryItemIds)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
