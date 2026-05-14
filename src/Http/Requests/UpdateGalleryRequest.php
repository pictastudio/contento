<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Database\Eloquent\{Collection, Model};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use PictaStudio\Contento\Http\Requests\Concerns\InteractsWithTranslatableInput;
use PictaStudio\Contento\Validations\Contracts\GalleryValidationRules;
use PictaStudio\Translatable\Locales;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class UpdateGalleryRequest extends FormRequest
{
    use InteractsWithTranslatableInput;

    private ?Collection $existingGalleryItems = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(GalleryValidationRules $validationRules): array
    {
        return $validationRules->getUpdateValidationRules();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty() || !is_array($this->input('gallery_items'))) {
                return;
            }

            $galleryId = $this->routeGalleryId();
            $seenIds = [];

            foreach ((array) $this->input('gallery_items', []) as $index => $galleryItemPayload) {
                if (!is_array($galleryItemPayload)) {
                    continue;
                }

                $payloadGalleryId = $galleryItemPayload['gallery_id'] ?? null;

                if (filled($payloadGalleryId) && (int) $payloadGalleryId !== $galleryId) {
                    $validator->errors()->add(
                        "gallery_items.{$index}.gallery_id",
                        'The selected gallery item gallery_id must match the gallery being updated.'
                    );
                }

                $galleryItemId = array_key_exists('id', $galleryItemPayload) && filled($galleryItemPayload['id'])
                    ? (int) $galleryItemPayload['id']
                    : null;

                if ($galleryItemId !== null) {
                    if (array_key_exists($galleryItemId, $seenIds)) {
                        $validator->errors()->add(
                            "gallery_items.{$index}.id",
                            'Duplicate gallery item id in nested payload.'
                        );
                    }

                    $seenIds[$galleryItemId] = true;

                    continue;
                }

                if ($this->hasTranslatablePayloadValue($galleryItemPayload, 'title')) {
                    continue;
                }

                $validator->errors()->add(
                    "gallery_items.{$index}.title",
                    'The title field is required when creating a gallery item via nested upsert.'
                );
            }

            $galleryItemIds = collect($seenIds)->keys();

            if ($galleryItemIds->isEmpty()) {
                return;
            }

            $existingGalleryItems = $this->existingGalleryItems();

            if ($existingGalleryItems->count() === $galleryItemIds->count()) {
                return;
            }

            $missingIds = $galleryItemIds
                ->diff($existingGalleryItems->keys())
                ->values()
                ->all();

            $validator->errors()->add(
                'gallery_items',
                'Some gallery items are not available for update: ' . implode(', ', $missingIds)
            );
        });
    }

    public function existingGalleryItems(): Collection
    {
        if ($this->existingGalleryItems instanceof Collection) {
            return $this->existingGalleryItems;
        }

        $galleryItemIds = collect($this->input('gallery_items', []))
            ->map(fn (mixed $galleryItem): mixed => is_array($galleryItem) ? ($galleryItem['id'] ?? null) : null)
            ->filter(fn (mixed $galleryItemId): bool => filled($galleryItemId))
            ->map(fn (mixed $galleryItemId): int => (int) $galleryItemId)
            ->unique()
            ->values()
            ->all();

        if ($galleryItemIds === []) {
            return $this->existingGalleryItems = new Collection;
        }

        $galleryItemModelClass = resolve_model('gallery_item');

        return $this->existingGalleryItems = $galleryItemModelClass::query()
            ->withoutGlobalScopes()
            ->where('gallery_id', $this->routeGalleryId())
            ->whereKey($galleryItemIds)
            ->get()
            ->keyBy(fn (mixed $galleryItem): int => (int) $galleryItem->getKey());
    }

    protected function prepareForValidation(): void
    {
        $this->prepareTranslatableInput(['title', 'slug', 'abstract']);
        $this->prepareGalleryItemPayloads();
    }

    private function prepareGalleryItemPayloads(): void
    {
        if (!$this->has('gallery_items') || !is_array($this->input('gallery_items'))) {
            return;
        }

        $payload = $this->all();
        $payload['gallery_items'] = collect($payload['gallery_items'] ?? [])
            ->map(function (mixed $galleryItemPayload): mixed {
                if (!is_array($galleryItemPayload)) {
                    return $galleryItemPayload;
                }

                $galleryItemPayload = $this->prepareTranslatablePayload($galleryItemPayload, [
                    'title',
                    'subtitle',
                    'description',
                ]);

                if (isset($galleryItemPayload['img']['metadata']) && is_array($galleryItemPayload['img']['metadata'])) {
                    $galleryItemPayload['img']['metadata'] = $this->nullEmptyStrings($galleryItemPayload['img']['metadata']);
                }

                return $galleryItemPayload;
            })
            ->all();

        $this->replace($payload);
    }

    private function prepareTranslatablePayload(array $payload, array $attributes): array
    {
        $translationsWrapper = config('translatable.translations_wrapper');
        $translations = is_string($translationsWrapper) && $translationsWrapper !== ''
            ? ($payload[$translationsWrapper] ?? null)
            : ($payload['translations'] ?? null);

        if (is_array($translations)) {
            foreach ($translations as $locale => $values) {
                if (!is_string($locale) || !is_array($values)) {
                    continue;
                }

                $existing = $payload[$locale] ?? null;

                $payload[$locale] = is_array($existing)
                    ? array_merge($values, $existing)
                    : $values;
            }
        }

        foreach (app(Locales::class)->all() as $locale) {
            $existing = $payload[$locale] ?? null;

            if (is_array($existing)) {
                $payload[$locale] = $existing;
            }

            foreach ($attributes as $attribute) {
                $flatKey = "{$attribute}:{$locale}";

                if (!array_key_exists($flatKey, $payload)) {
                    continue;
                }

                $payload[$locale][$attribute] = $payload[$flatKey];
            }
        }

        return $payload;
    }

    private function hasTranslatablePayloadValue(array $payload, string $attribute): bool
    {
        if ($this->isFilledTranslatableValue($payload[$attribute] ?? null)) {
            return true;
        }

        foreach (app(Locales::class)->all() as $locale) {
            if ($this->isFilledTranslatableValue($payload["{$attribute}:{$locale}"] ?? null)) {
                return true;
            }

            $localePayload = $payload[$locale] ?? null;

            if (is_array($localePayload) && $this->isFilledTranslatableValue($localePayload[$attribute] ?? null)) {
                return true;
            }

            $translationsWrapper = config('translatable.translations_wrapper');
            $translations = is_string($translationsWrapper) && $translationsWrapper !== ''
                ? ($payload[$translationsWrapper] ?? null)
                : ($payload['translations'] ?? null);

            if (
                is_array($translations)
                && $this->isFilledTranslatableValue(data_get($translations, "{$locale}.{$attribute}"))
            ) {
                return true;
            }
        }

        return false;
    }

    private function isFilledTranslatableValue(mixed $value): bool
    {
        if (is_string($value)) {
            return mb_trim($value) !== '';
        }

        if (is_array($value)) {
            return collect($value)
                ->flatten()
                ->contains(fn (mixed $nestedValue): bool => $this->isFilledTranslatableValue($nestedValue));
        }

        return filled($value);
    }

    private function nullEmptyStrings(array $values): array
    {
        return array_map(function (mixed $value): mixed {
            if (is_array($value)) {
                return $this->nullEmptyStrings($value);
            }

            return $value === '' ? null : $value;
        }, $values);
    }

    private function routeGalleryId(): int
    {
        $gallery = $this->route('gallery');

        if ($gallery instanceof Model) {
            return (int) $gallery->getKey();
        }

        return (int) $gallery;
    }
}
