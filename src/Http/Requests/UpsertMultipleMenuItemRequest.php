<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use PictaStudio\Contento\Validations\Contracts\MenuItemValidationRules;
use PictaStudio\Translatable\Locales;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class UpsertMultipleMenuItemRequest extends FormRequest
{
    private ?Collection $existingMenuItems = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(MenuItemValidationRules $validationRules): array
    {
        return $validationRules->getBulkUpsertValidationRules();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $seenIds = [];

            foreach ((array) $this->input('menu_items', []) as $index => $menuItemPayload) {
                if (!is_array($menuItemPayload)) {
                    continue;
                }

                $menuItemId = array_key_exists('id', $menuItemPayload) && filled($menuItemPayload['id'])
                    ? (int) $menuItemPayload['id']
                    : null;

                if ($menuItemId !== null) {
                    if (array_key_exists($menuItemId, $seenIds)) {
                        $validator->errors()->add(
                            "menu_items.{$index}.id",
                            'Duplicate menu item id in bulk payload.'
                        );
                    }

                    $seenIds[$menuItemId] = true;

                    continue;
                }

                if (!array_key_exists('menu_id', $menuItemPayload) || blank($menuItemPayload['menu_id'])) {
                    $validator->errors()->add(
                        "menu_items.{$index}.menu_id",
                        'The menu_id field is required when creating a menu item via bulk upsert.'
                    );
                }

                if ($this->hasTranslatablePayloadValue($menuItemPayload, 'title')) {
                    continue;
                }

                $validator->errors()->add(
                    "menu_items.{$index}.title",
                    'The title field is required when creating a menu item via bulk upsert.'
                );
            }
        });
    }

    public function existingMenuItems(): Collection
    {
        if ($this->existingMenuItems instanceof Collection) {
            return $this->existingMenuItems;
        }

        $menuItemIds = collect($this->input('menu_items', []))
            ->map(fn (mixed $menuItem): mixed => is_array($menuItem) ? ($menuItem['id'] ?? null) : null)
            ->filter(fn (mixed $menuItemId): bool => filled($menuItemId))
            ->map(fn (mixed $menuItemId): int => (int) $menuItemId)
            ->unique()
            ->values()
            ->all();

        if ($menuItemIds === []) {
            return $this->existingMenuItems = new Collection;
        }

        $menuItemModelClass = resolve_model('menu_item');

        return $this->existingMenuItems = $menuItemModelClass::query()
            ->withoutGlobalScopes()
            ->whereKey($menuItemIds)
            ->get()
            ->keyBy(fn (mixed $menuItem): int => (int) $menuItem->getKey());
    }

    protected function prepareForValidation(): void
    {
        $payload = $this->all();
        $jsonPayload = $this->json()->all();

        if (is_array($jsonPayload) && array_is_list($jsonPayload)) {
            $payload = [
                ...$payload,
                'menu_items' => $jsonPayload,
            ];
        }

        $payload['menu_items'] = collect($payload['menu_items'] ?? [])
            ->map(function (mixed $menuItemPayload): mixed {
                if (!is_array($menuItemPayload)) {
                    return $menuItemPayload;
                }

                return $this->prepareTranslatablePayload($menuItemPayload, ['title', 'link']);
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
}
