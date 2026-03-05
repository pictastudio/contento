<?php

namespace PictaStudio\Contento\Http\Requests\Concerns;

use PictaStudio\Translatable\Locales;

trait InteractsWithTranslatableInput
{
    /**
     * @param  array<int, string>  $attributes
     */
    protected function prepareTranslatableInput(array $attributes): void
    {
        $locales = app(Locales::class)->all();
        $payload = [];

        foreach ($locales as $locale) {
            $existing = $this->input($locale);

            if (is_array($existing)) {
                $payload[$locale] = $existing;
            }

            foreach ($attributes as $attribute) {
                $flatKey = "{$attribute}:{$locale}";

                if (array_key_exists($flatKey, $this->all())) {
                    $payload[$locale][$attribute] = $this->input($flatKey);
                }
            }
        }

        if ($payload !== []) {
            $this->merge($payload);
        }
    }

    /**
     * @param  array<int, string>  $attributes
     * @return array<int, string>
     */
    protected function localeAttributeKeys(array $attributes): array
    {
        $keys = [];

        foreach (app(Locales::class)->all() as $locale) {
            foreach ($attributes as $attribute) {
                $keys[] = "{$locale}.{$attribute}";
            }
        }

        return $keys;
    }

    protected function hasTranslatableValue(string $attribute): bool
    {
        foreach (app(Locales::class)->all() as $locale) {
            $value = $this->input("{$locale}.{$attribute}");

            if (is_string($value) && mb_trim($value) !== '') {
                return true;
            }
        }

        return false;
    }
}
