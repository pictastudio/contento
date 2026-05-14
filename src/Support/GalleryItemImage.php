<?php

namespace PictaStudio\Contento\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\{Arr, Str};
use Illuminate\Validation\ValidationException;

class GalleryItemImage
{
    public static function normalize(mixed $item, array &$usedIds = []): ?array
    {
        if (is_string($item)) {
            $item = json_decode($item, true) ?: null;
        }

        if (!is_array($item) || blank(Arr::get($item, 'src'))) {
            return null;
        }

        return [
            'id' => self::resolveUniqueId(Arr::get($item, 'id'), $usedIds),
            'name' => Arr::get($item, 'name'),
            'alt' => Arr::get($item, 'alt'),
            'mimetype' => Arr::get($item, 'mimetype'),
            'src' => Arr::get($item, 'src'),
            'metadata' => Arr::get($item, 'metadata'),
        ];
    }

    public static function merge(Model $model, mixed $currentImage, mixed $payload): ?array
    {
        if ($payload === null) {
            return null;
        }

        if (!is_array($payload)) {
            return self::normalize($currentImage);
        }

        $usedIds = [];
        $currentImage = self::normalize($currentImage, $usedIds);

        if (Arr::get($payload, 'file') instanceof UploadedFile) {
            /** @var UploadedFile $file */
            $file = Arr::get($payload, 'file');

            return [
                'id' => is_array($currentImage) && filled(Arr::get($currentImage, 'id'))
                    ? (string) Arr::get($currentImage, 'id')
                    : self::generateUniqueId($usedIds),
                'name' => Arr::get($payload, 'name'),
                'alt' => Arr::get($payload, 'alt'),
                'mimetype' => Arr::get($payload, 'mimetype', $file->getMimeType()),
                'src' => $file->store(self::storagePath($model), 'public'),
                'metadata' => Arr::get($payload, 'metadata'),
            ];
        }

        if (!is_array($currentImage)) {
            return null;
        }

        $updated = $currentImage;

        foreach (['name', 'alt', 'mimetype', 'metadata'] as $attribute) {
            if (array_key_exists($attribute, $payload)) {
                $updated[$attribute] = Arr::get($payload, $attribute);
            }
        }

        return self::normalize($updated);
    }

    public static function validatePayload(mixed $payload, mixed $currentImage = null, string $attribute = 'img'): void
    {
        if ($payload === null) {
            return;
        }

        $payload = is_array($payload) ? $payload : [];
        $currentImage = self::normalize($currentImage);
        $hasFile = Arr::get($payload, 'file') instanceof UploadedFile;
        $imageId = self::scalarString(Arr::get($payload, 'id'));
        $existingId = is_array($currentImage) ? self::scalarString(Arr::get($currentImage, 'id')) : null;
        $hasExistingImage = filled($existingId);
        $hasExistingId = blank($imageId)
            ? $hasExistingImage
            : filled($existingId) && $imageId === $existingId;

        $errors = [];

        if (!$hasFile && !$hasExistingId) {
            $errors["{$attribute}.file"] = ['The file field is required when the selected image does not exist yet.'];
        }

        if (filled($imageId) && !$hasExistingId) {
            $errors["{$attribute}.id"] = ['The selected image id is invalid.'];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private static function resolveUniqueId(mixed $id, array &$usedIds): string
    {
        if (is_scalar($id)) {
            $candidate = (string) $id;

            if (filled($candidate) && !in_array($candidate, $usedIds, true)) {
                $usedIds[] = $candidate;

                return $candidate;
            }
        }

        return self::generateUniqueId($usedIds);
    }

    private static function generateUniqueId(array &$usedIds = []): string
    {
        do {
            $id = (string) Str::ulid();
        } while (in_array($id, $usedIds, true));

        $usedIds[] = $id;

        return $id;
    }

    private static function storagePath(Model $model): string
    {
        return implode('/', [
            'gallery_items',
            (string) $model->getKey(),
            'img',
        ]);
    }

    private static function scalarString(mixed $value): ?string
    {
        return is_scalar($value) ? (string) $value : null;
    }
}
