<?php

namespace PictaStudio\Contento\Http\Resources;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContentoAnonymousResourceCollection extends AnonymousResourceCollection
{
    public static $wrap = 'data';

    public function __construct($resource, $collects)
    {
        static::configureWrapping();

        parent::__construct($resource, $collects);
    }

    protected static function configureWrapping(): void
    {
        static::$wrap = (bool) config('contento.routes.api.json_resource_enable_wrapping', true)
            ? 'data'
            : null;
    }
}
