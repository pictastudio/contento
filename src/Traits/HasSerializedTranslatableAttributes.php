<?php

namespace PictaStudio\Contento\Traits;

use PictaStudio\Translatable\{Translatable, Translation};

trait HasSerializedTranslatableAttributes
{
    use Translatable {
        getTranslationValue as protected getStoredTranslationValue;
        setTranslationValue as protected setStoredTranslationValue;
    }

    public function getTranslationValue(string $locale, string $attribute): mixed
    {
        $value = $this->getStoredTranslationValue($locale, $attribute);

        if (!in_array($attribute, $this->serializedTranslatableAttributes(), true) || !is_string($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    public function setTranslationValue(
        string $locale,
        string $attribute,
        mixed $value,
        ?string $generatedBy = Translation::GENERATED_BY_USER,
    ): void {
        $storedValue = in_array($attribute, $this->serializedTranslatableAttributes(), true)
            ? $this->serializeTranslatedAttributeValue($value)
            : $value;

        $this->setStoredTranslationValue($locale, $attribute, $storedValue, $generatedBy);

        if (
            in_array($attribute, $this->serializedTranslatableAttributes(), true)
            && $this->shouldSyncTranslatedAttributeToBaseColumn($attribute)
            && $this->shouldOverrideBaseColumnValue($attribute, $locale)
        ) {
            parent::setAttribute($attribute, $value);
        }
    }

    /**
     * @return array<int, string>
     */
    protected function serializedTranslatableAttributes(): array
    {
        return [];
    }

    protected function serializeTranslatedAttributeValue(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
