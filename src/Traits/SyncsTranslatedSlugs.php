<?php

namespace PictaStudio\Contento\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use PictaStudio\Translatable\Locales;
use PictaStudio\Translatable\Translation;

trait SyncsTranslatedSlugs
{
    protected function syncTranslatedSlugs(): void
    {
        if (!$this->supportsTranslatedSlugSync()) {
            return;
        }

        $currentLocale = $this->currentTranslatableLocale();
        $baseSlug = $this->normalizeTranslatedSlugValue(parent::getAttribute('slug'));

        if ($baseSlug !== '') {
            $this->setTranslationValue($currentLocale, 'slug', $baseSlug);
        }

        foreach ($this->translatedSlugLocales() as $locale) {
            if ($locale === $currentLocale && $baseSlug !== '') {
                continue;
            }

            $source = $this->normalizeTranslatedSlugValue($this->getTranslationValue($locale, $this->translatedSlugSourceAttribute()));
            if ($source === '') {
                continue;
            }

            $slug = Str::slug($source);
            if ($slug === '') {
                continue;
            }

            $this->setTranslationValue($locale, 'slug', $this->makeUniqueTranslatedSlug($slug, $locale));
        }
    }

    protected function supportsTranslatedSlugSync(): bool
    {
        if (!method_exists($this, 'isTranslationAttribute') || !method_exists($this, 'setTranslationValue') || !method_exists($this, 'getTranslationValue')) {
            return false;
        }

        if (!$this->isTranslationAttribute('slug')) {
            return false;
        }

        $sourceAttribute = $this->translatedSlugSourceAttribute();

        return $sourceAttribute !== '' && $this->isTranslationAttribute($sourceAttribute);
    }

    protected function translatedSlugSourceAttribute(): string
    {
        return 'title';
    }

    /**
     * @return array<int, string>
     */
    protected function translatedSlugLocales(): array
    {
        if (!app()->bound(Locales::class)) {
            return [app()->getLocale()];
        }

        return app(Locales::class)->all();
    }

    protected function currentTranslatableLocale(): string
    {
        if (!app()->bound(Locales::class)) {
            return app()->getLocale();
        }

        return app(Locales::class)->current();
    }

    protected function normalizeTranslatedSlugValue(mixed $value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        return mb_trim((string) $value);
    }

    protected function makeUniqueTranslatedSlug(string $baseSlug, string $locale): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while ($this->translatedSlugExists($slug, $locale)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function translatedSlugExists(string $slug, string $locale): bool
    {
        $translationModel = $this->translatedSlugTranslationModelClass();
        $localeKey = $this->translatedSlugLocaleKey();

        $translationQuery = $translationModel::query()
            ->where('translatable_type', $this->getMorphClass())
            ->where($localeKey, $locale)
            ->where('attribute', 'slug')
            ->where('value', $slug);

        if ($this->exists && $this->getKey() !== null) {
            $translationQuery->where('translatable_id', '!=', $this->getKey());
        }

        if ($translationQuery->exists()) {
            return true;
        }

        $baseSlugQuery = $this->newQuery()->where('slug', $slug);
        if ($this->exists && $this->getKey() !== null) {
            $baseSlugQuery->whereKeyNot($this->getKey());
        }

        return $baseSlugQuery->exists();
    }

    /**
     * @return class-string<Model>
     */
    protected function translatedSlugTranslationModelClass(): string
    {
        $modelClass = config('translatable.translation_model', Translation::class);

        return is_string($modelClass) && class_exists($modelClass)
            ? $modelClass
            : Translation::class;
    }

    protected function translatedSlugLocaleKey(): string
    {
        $localeKey = config('translatable.locale_key', 'locale');

        return is_string($localeKey) && $localeKey !== '' ? $localeKey : 'locale';
    }
}
