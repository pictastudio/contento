<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
}
