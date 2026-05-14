<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use PictaStudio\Contento\Support\{CatalogImage, GalleryItemImage};

abstract class ContentoJsonResource extends JsonResource
{
    public static $wrap = 'data';

    public function __construct($resource)
    {
        static::configureWrapping();

        parent::__construct($resource);
    }

    protected static function newCollection($resource): ContentoAnonymousResourceCollection
    {
        static::configureWrapping();

        return new ContentoAnonymousResourceCollection($resource, static::class);
    }

    protected static function configureWrapping(): void
    {
        static::$wrap = (bool) config('contento.routes.api.json_resource_enable_wrapping', true)
            ? 'data'
            : null;
    }

    protected function transformCatalogImageCollection(mixed $items): array
    {
        return collect(CatalogImage::normalizeCollection($items))
            ->map(fn (array $item): array => [
                'id' => Arr::get($item, 'id'),
                'type' => Arr::get($item, 'type'),
                'name' => Arr::get($item, 'name'),
                'alt' => Arr::get($item, 'alt'),
                'mimetype' => Arr::get($item, 'mimetype'),
                'sort_order' => Arr::get($item, 'sort_order'),
                'src' => $this->getImageAssetUrl(Arr::get($item, 'src')),
            ])
            ->values()
            ->all();
    }

    protected function transformGalleryItemImage(mixed $item): ?array
    {
        $usedIds = [];
        $item = GalleryItemImage::normalize($item, $usedIds);

        if (!is_array($item)) {
            return null;
        }

        return [
            'id' => Arr::get($item, 'id'),
            'name' => Arr::get($item, 'name'),
            'alt' => Arr::get($item, 'alt'),
            'mimetype' => Arr::get($item, 'mimetype'),
            'src' => Arr::get($item, 'src'),
            'url' => $this->getImageAssetUrl(Arr::get($item, 'src')),
            'metadata' => Arr::get($item, 'metadata'),
        ];
    }

    protected function getImageAssetUrl(?string $image): ?string
    {
        if (blank($image)) {
            return null;
        }

        return URL::isValidUrl($image) ? $image : asset('storage/' . $image);
    }
}
