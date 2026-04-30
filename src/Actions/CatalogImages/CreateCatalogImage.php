<?php

namespace PictaStudio\Contento\Actions\CatalogImages;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use function PictaStudio\Contento\Helpers\Functions\query;

class CreateCatalogImage
{
    public function handle(array $payload): Model
    {
        return DB::transaction(function () use ($payload): Model {
            /** @var UploadedFile $file */
            $file = Arr::pull($payload, 'file');

            return query('catalog_image')->create([
                ...$payload,
                ...CatalogImageFile::attributesFor($file),
            ]);
        });
    }
}
