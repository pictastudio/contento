<?php

namespace PictaStudio\Contento\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use PictaStudio\Contento\Models\Gallery;

class GalleryUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Gallery $gallery) {}
}
