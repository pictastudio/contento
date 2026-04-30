<?php

namespace PictaStudio\Contento\Actions\CatalogImages;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateCatalogImage
{
    public function handle(Model $catalogImage, array $payload): Model
    {
        return DB::transaction(function () use ($catalogImage, $payload): Model {
            $oldDisk = (string) $catalogImage->getAttribute('disk');
            $oldPath = (string) $catalogImage->getAttribute('path');
            $file = Arr::pull($payload, 'file');

            if ($file instanceof UploadedFile) {
                $payload = [
                    ...$payload,
                    ...CatalogImageFile::attributesFor($file),
                ];
            }

            $catalogImage->fill($payload);
            $catalogImage->save();

            if ($file instanceof UploadedFile) {
                CatalogImageFile::deleteIfConfigured($oldDisk, $oldPath);
            }

            return $catalogImage->refresh();
        });
    }
}
