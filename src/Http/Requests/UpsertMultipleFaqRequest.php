<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use PictaStudio\Contento\Validations\Contracts\FaqValidationRules;
use PictaStudio\Translatable\Locales;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class UpsertMultipleFaqRequest extends FormRequest
{
    private ?Collection $existingFaqs = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(FaqValidationRules $validationRules): array
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

            foreach ((array) $this->input('faqs', []) as $index => $faqPayload) {
                if (!is_array($faqPayload)) {
                    continue;
                }

                $faqId = array_key_exists('id', $faqPayload) && filled($faqPayload['id'])
                    ? (int) $faqPayload['id']
                    : null;

                if ($faqId !== null) {
                    if (array_key_exists($faqId, $seenIds)) {
                        $validator->errors()->add(
                            "faqs.{$index}.id",
                            'Duplicate faq id in bulk payload.'
                        );
                    }

                    $seenIds[$faqId] = true;

                    continue;
                }

                if ($this->hasTranslatablePayloadValue($faqPayload, 'title')) {
                    continue;
                }

                $validator->errors()->add(
                    "faqs.{$index}.title",
                    'The title field is required when creating a faq via bulk upsert.'
                );
            }
        });
    }

    public function existingFaqs(): Collection
    {
        if ($this->existingFaqs instanceof Collection) {
            return $this->existingFaqs;
        }

        $faqIds = collect($this->input('faqs', []))
            ->map(fn (mixed $faq): mixed => is_array($faq) ? ($faq['id'] ?? null) : null)
            ->filter(fn (mixed $faqId): bool => filled($faqId))
            ->map(fn (mixed $faqId): int => (int) $faqId)
            ->unique()
            ->values()
            ->all();

        if ($faqIds === []) {
            return $this->existingFaqs = new Collection;
        }

        $faqModelClass = resolve_model('faq');

        return $this->existingFaqs = $faqModelClass::query()
            ->withoutGlobalScopes()
            ->whereKey($faqIds)
            ->get()
            ->keyBy(fn (mixed $faq): int => (int) $faq->getKey());
    }

    protected function prepareForValidation(): void
    {
        $payload = $this->all();
        $jsonPayload = $this->json()->all();

        if (is_array($jsonPayload) && array_is_list($jsonPayload)) {
            $payload = [
                ...$payload,
                'faqs' => $jsonPayload,
            ];
        }

        $payload['faqs'] = collect($payload['faqs'] ?? [])
            ->map(function (mixed $faqPayload): mixed {
                if (!is_array($faqPayload)) {
                    return $faqPayload;
                }

                $faqPayload = $this->prepareTranslatablePayload($faqPayload, ['title', 'content', 'slug']);

                $isCreate = !array_key_exists('id', $faqPayload) || blank($faqPayload['id']);

                if ($isCreate && !array_key_exists('content', $faqPayload)) {
                    $faqPayload['content'] = '';
                }

                return $faqPayload;
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
