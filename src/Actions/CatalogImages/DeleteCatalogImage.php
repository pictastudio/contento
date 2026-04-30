<?php

namespace PictaStudio\Contento\Actions\CatalogImages;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DeleteCatalogImage
{
    public function handle(Model $catalogImage): void
    {
        DB::transaction(function () use ($catalogImage): void {
            $disk = (string) $catalogImage->getAttribute('disk');
            $path = (string) $catalogImage->getAttribute('path');

            $catalogImage->delete();

            CatalogImageFile::deleteIfConfigured($disk, $path);
        });
    }
}
