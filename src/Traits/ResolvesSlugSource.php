<?php

namespace PictaStudio\Contento\Traits;

use PictaStudio\Translatable\Locales;

trait ResolvesSlugSource
{
    protected function resolveSlugSource(string $attribute): string
    {
        $source = $this->normalizeSlugSourceValue(data_get($this, $attribute));

        if ($source !== '') {
            return $source;
        }

        if (
            ! method_exists($this, 'isTranslationAttribute')
            || ! method_exists($this, 'getTranslationValue')
            || ! $this->isTranslationAttribute($attribute)
        ) {
            return '';
        }

        foreach ($this->slugSourceLocales() as $locale) {
            $translatedSource = $this->normalizeSlugSourceValue($this->getTranslationValue($locale, $attribute));

            if ($translatedSource !== '') {
                return $translatedSource;
            }
        }

        return '';
    }

    protected function slugSourceLocales(): array
    {
        $locales = app(Locales::class);
        $fallbackLocale = config('translatable.fallback_locale');

        $candidates = [
            app()->getLocale(),
            $locales->current(),
            is_string($fallbackLocale) ? $fallbackLocale : null,
            ...$locales->all(),
        ];

        return array_values(array_unique(array_filter($candidates, fn ($locale) => is_string($locale) && $locale !== '')));
    }

    protected function normalizeSlugSourceValue(mixed $value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        return trim((string) $value);
    }
}
