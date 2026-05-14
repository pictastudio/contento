<?php

namespace PictaStudio\Contento\Actions\CatalogImages;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CatalogImageFile
{
    public static function attributesFor(UploadedFile $file): array
    {
        $disk = self::disk();
        $path = $file->store(self::directory(), $disk);
        $dimensions = self::dimensions($file);

        return [
            'disk' => $disk,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
        ];
    }

    public static function deleteIfConfigured(string $disk, string $path): void
    {
        if (!(bool) config('contento.catalog_images.delete_file_on_destroy', true) || $disk === '' || $path === '') {
            return;
        }

        Storage::disk($disk)->delete($path);
    }

    private static function disk(): string
    {
        return (string) config('contento.catalog_images.disk', 'public');
    }

    private static function directory(): string
    {
        return implode('/', array_filter([
            mb_trim((string) config('contento.catalog_images.directory', 'catalog_images'), '/'),
            now()->format('Y/m/d'),
        ]));
    }

    private static function dimensions(UploadedFile $file): array
    {
        $imageSize = @getimagesize($file->getPathname());

        if (!is_array($imageSize)) {
            return [
                'width' => null,
                'height' => null,
            ];
        }

        return [
            'width' => $imageSize[0] ?? null,
            'height' => $imageSize[1] ?? null,
        ];
    }
}
