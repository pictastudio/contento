<?php

namespace PictaStudio\Contento\Traits;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\SlugOptions;

trait EnsuresSlug
{
    protected static function bootEnsuresSlug(): void
    {
        static::saving(function (Model $model): void {
            if (!method_exists($model, 'generateSlug') || !method_exists($model, 'getSlugOptions')) {
                return;
            }

            $slugOptions = $model->getSlugOptions();

            if ($slugOptions->skipGenerate) {
                return;
            }

            if ($model->exists && !$slugOptions->generateSlugsOnUpdate) {
                return;
            }

            if (!$model->exists && !$slugOptions->generateSlugsOnCreate) {
                return;
            }

            $slugField = $slugOptions->slugField;
            if ($slugOptions->preventOverwrite && $model->{$slugField} !== null) {
                return;
            }

            if ($model->resolveSlugSourceForOptions($slugOptions) !== '') {
                $model->generateSlug();
            }

            if (method_exists($model, 'syncTranslatedSlugs')) {
                $model->syncTranslatedSlugs();
            }
        });
    }

    protected function resolveSlugSourceForOptions(SlugOptions $slugOptions): string
    {
        $source = $slugOptions->generateSlugFrom;

        if (is_callable($source)) {
            $value = call_user_func($source, $this);

            return $this->normalizeSlugSource($value);
        }

        if (!is_array($source)) {
            return '';
        }

        $values = [];
        foreach ($source as $fieldName) {
            if (!is_string($fieldName)) {
                continue;
            }

            $value = $this->normalizeSlugSource(data_get($this, $fieldName));
            if ($value !== '') {
                $values[] = $value;
            }
        }

        return implode($slugOptions->slugSeparator, $values);
    }

    protected function normalizeSlugSource(mixed $value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        return mb_trim((string) $value);
    }
}
