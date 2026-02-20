<?php

namespace PictaStudio\Contento\Traits;

use Illuminate\Database\Eloquent\Model;
use PictaStudio\Translatable\Locales;
use PictaStudio\Translatable\Translation;

trait HasSlugRouteBinding
{
    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $stringValue = (string) $value;

        if ($stringValue !== '' && ctype_digit($stringValue)) {
            $byId = $this->newQuery()->whereKey((int) $stringValue)->first();
            if ($byId !== null) {
                return $byId;
            }
        }

        $localizedMatch = $this->resolveByTranslatedSlug($stringValue, $this->slugRouteLocales());
        if ($localizedMatch !== null) {
            return $localizedMatch;
        }

        $baseMatch = $this->newQuery()->where('slug', $stringValue)->first();
        if ($baseMatch !== null) {
            return $baseMatch;
        }

        return $this->resolveByTranslatedSlug($stringValue);
    }

    /**
     * @param  array<int, string>|null  $locales
     */
    protected function resolveByTranslatedSlug(string $slug, ?array $locales = null): ?Model
    {
        if ($slug === '' || !$this->supportsTranslatedSlugRouteBinding()) {
            return null;
        }

        $translationModel = $this->translationModelClass();
        $localeKey = $this->translationLocaleKey();

        if (is_array($locales) && $locales !== []) {
            foreach ($locales as $locale) {
                $translation = $translationModel::query()
                    ->where('translatable_type', $this->getMorphClass())
                    ->where($localeKey, $locale)
                    ->where('attribute', 'slug')
                    ->where('value', $slug)
                    ->first();

                if ($translation !== null) {
                    return $this->newQuery()->whereKey($translation->getAttribute('translatable_id'))->first();
                }
            }

            return null;
        }

        $translation = $translationModel::query()
            ->where('translatable_type', $this->getMorphClass())
            ->where('attribute', 'slug')
            ->where('value', $slug)
            ->first();

        if ($translation === null) {
            return null;
        }

        return $this->newQuery()->whereKey($translation->getAttribute('translatable_id'))->first();
    }

    /**
     * @return array<int, string>
     */
    protected function slugRouteLocales(): array
    {
        $fallbackLocale = config('translatable.fallback_locale');

        if (!app()->bound(Locales::class)) {
            $current = app()->getLocale();

            return array_values(array_unique(array_filter([
                is_string($current) ? $current : null,
                is_string($fallbackLocale) ? $fallbackLocale : null,
            ])));
        }

        $locales = app(Locales::class);

        return array_values(array_unique(array_filter([
            app()->getLocale(),
            $locales->current(),
            is_string($fallbackLocale) ? $fallbackLocale : null,
        ], fn ($locale) => is_string($locale) && $locale !== '')));
    }

    protected function supportsTranslatedSlugRouteBinding(): bool
    {
        return method_exists($this, 'isTranslationAttribute')
            && $this->isTranslationAttribute('slug');
    }

    /**
     * @return class-string<Model>
     */
    protected function translationModelClass(): string
    {
        $modelClass = config('translatable.translation_model', Translation::class);

        return is_string($modelClass) && class_exists($modelClass)
            ? $modelClass
            : Translation::class;
    }

    protected function translationLocaleKey(): string
    {
        $localeKey = config('translatable.locale_key', 'locale');

        return is_string($localeKey) && $localeKey !== '' ? $localeKey : 'locale';
    }
}
