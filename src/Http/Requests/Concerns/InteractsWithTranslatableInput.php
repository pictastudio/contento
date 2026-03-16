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
        $translationsWrapper = config('translatable.translations_wrapper');
        $translations = is_string($translationsWrapper) && $translationsWrapper !== ''
            ? $this->input($translationsWrapper)
            : $this->input('translations');

        if (is_array($translations)) {
            $preparedTranslations = [];

            foreach ($translations as $locale => $values) {
                if (!is_string($locale) || !is_array($values)) {
                    continue;
                }

                $existing = $this->input($locale);

                $preparedTranslations[$locale] = is_array($existing)
                    ? array_merge($values, $existing)
                    : $values;
            }

            if ($preparedTranslations !== []) {
                $this->merge($preparedTranslations);
            }
        }

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
        if ($this->isFilledTranslatableValue($this->input($attribute))) {
            return true;
        }

        foreach (app(Locales::class)->all() as $locale) {
            if ($this->isFilledTranslatableValue($this->input("{$attribute}:{$locale}"))) {
                return true;
            }

            $value = $this->input("{$locale}.{$attribute}");

            if ($this->isFilledTranslatableValue($value)) {
                return true;
            }

            $translationsWrapper = config('translatable.translations_wrapper');
            $translations = is_string($translationsWrapper) && $translationsWrapper !== ''
                ? $this->input($translationsWrapper)
                : $this->input('translations');

            if ($this->isFilledTranslatableValue(data_get($translations, "{$locale}.{$attribute}"))) {
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
