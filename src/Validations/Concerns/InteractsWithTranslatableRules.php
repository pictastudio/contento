<?php

namespace PictaStudio\Contento\Validations\Concerns;

use PictaStudio\Translatable\Locales;

trait InteractsWithTranslatableRules
{
    /**
     * @param  array<string, array<int, mixed>>  $attributesRules
     * @param  array<int, string>  $allowedLocalizedAttributes
     */
    protected function translatableLocaleRules(
        array $attributesRules,
        array $allowedLocalizedAttributes
    ): array {
        $rules = [];

        foreach ($this->translatableLocales() as $locale) {
            $rules[$locale] = ['sometimes', 'array:' . implode(',', $allowedLocalizedAttributes)];

            foreach ($attributesRules as $attribute => $attributeRules) {
                $rules[$locale . '.' . $attribute] = $attributeRules;
                $rules[$attribute . ':' . $locale] = $attributeRules;
            }
        }

        return $rules;
    }

    /**
     * @return array<int, string>
     */
    protected function translatableLocales(): array
    {
        $locales = app(Locales::class)->all();

        if ($locales === []) {
            return [app()->getLocale()];
        }

        return collect($locales)
            ->filter(fn (mixed $locale): bool => is_string($locale) && filled($locale))
            ->values()
            ->all();
    }
}
