<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use PictaStudio\Contento\Http\Requests\StoreFaqCategoryRequest;
use PictaStudio\Contento\Http\Resources\FaqCategoryResource;
use PictaStudio\Contento\Models\FaqCategory;
use PictaStudio\Translatable\{Locales, Translation};

class FaqCategoryController extends BaseController
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', FaqCategory::class);

        $categories = FaqCategory::with('faqs')->paginate();

        return FaqCategoryResource::collection($categories);
    }

    public function show(FaqCategory $faqCategory): JsonResource
    {
        $this->authorizeIfConfigured('view', $faqCategory);

        return FaqCategoryResource::make($faqCategory->load('faqs'));
    }

    public function store(StoreFaqCategoryRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', FaqCategory::class);

        $data = $request->validated();
        $category = new FaqCategory;
        $category->fill($data);
        $category->generateSlug();
        $category->save();
        $this->syncTranslations($category, $data);

        return FaqCategoryResource::make($category->load('faqs'));
    }

    public function update(StoreFaqCategoryRequest $request, FaqCategory $faqCategory): JsonResource
    {
        $this->authorizeIfConfigured('update', $faqCategory);

        $data = $request->validated();
        $faqCategory->fill($data);
        $faqCategory->generateSlug();
        $faqCategory->save();
        $this->syncTranslations($faqCategory, $data);

        return FaqCategoryResource::make($faqCategory->load('faqs'));
    }

    public function destroy(FaqCategory $faqCategory): Response
    {
        $this->authorizeIfConfigured('delete', $faqCategory);

        $faqCategory->delete();

        return response()->noContent();
    }

    protected function syncTranslations(FaqCategory $category, array $data): void
    {
        $locale = app()->getLocale();
        $locales = app(Locales::class);
        $translationModel = config('translatable.translation_model', Translation::class);
        $localeKey = config('translatable.locale_key', 'locale');

        $persist = function (string $targetLocale, string $attribute, mixed $value) use ($category, $translationModel, $localeKey): void {
            $translation = $translationModel::query()
                ->where('translatable_type', $category->getMorphClass())
                ->where('translatable_id', $category->getKey())
                ->where($localeKey, $targetLocale)
                ->where('attribute', $attribute)
                ->first() ?? new $translationModel;

            $timestamp = $translation->freshTimestamp();
            $createdAtColumn = $translation->getCreatedAtColumn();
            $updatedAtColumn = $translation->getUpdatedAtColumn();

            $translation->setAttribute('translatable_type', $category->getMorphClass());
            $translation->setAttribute('translatable_id', $category->getKey());
            $translation->setAttribute($localeKey, $targetLocale);
            $translation->setAttribute('attribute', $attribute);
            $translation->setAttribute('value', $value);

            if (
                !$translation->exists
                && is_string($createdAtColumn)
                && $createdAtColumn !== ''
                && $translation->getAttribute($createdAtColumn) === null
            ) {
                $translation->setAttribute($createdAtColumn, $timestamp);
            }

            if (is_string($updatedAtColumn) && $updatedAtColumn !== '') {
                $translation->setAttribute($updatedAtColumn, $timestamp);
            }

            $translation->save();
        };

        foreach (['title', 'abstract'] as $attribute) {
            if (array_key_exists($attribute, $data)) {
                $persist($locale, $attribute, $data[$attribute]);

                if ($attribute === 'title') {
                    $slug = Str::slug((string) $data[$attribute]);
                    if ($slug !== '') {
                        $persist($locale, 'slug', $this->makeUniqueLocalizedSlug($category, $translationModel, $locale, $slug));
                    }
                }
            }
        }

        foreach ($data as $targetLocale => $translatedValues) {
            if (!is_array($translatedValues) || !$locales->has($targetLocale)) {
                continue;
            }

            foreach (['title', 'abstract'] as $attribute) {
                if (array_key_exists($attribute, $translatedValues)) {
                    $persist($targetLocale, $attribute, $translatedValues[$attribute]);

                    if ($attribute === 'title') {
                        $slug = Str::slug((string) $translatedValues[$attribute]);
                        if ($slug !== '') {
                            $persist($targetLocale, 'slug', $this->makeUniqueLocalizedSlug($category, $translationModel, $targetLocale, $slug));
                        }
                    }
                }
            }
        }

    }

    protected function makeUniqueLocalizedSlug(FaqCategory $category, string $translationModel, string $locale, string $baseSlug): string
    {
        $slug = $baseSlug;
        $suffix = 1;

        while ($this->localizedSlugExists($category, $translationModel, $locale, $slug)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function localizedSlugExists(FaqCategory $category, string $translationModel, string $locale, string $slug): bool
    {
        $query = $translationModel::query()
            ->where('translatable_type', $category->getMorphClass())
            ->where('locale', $locale)
            ->where('attribute', 'slug')
            ->where('value', $slug)
            ->where('translatable_id', '!=', $category->getKey());

        if ($query->exists()) {
            return true;
        }

        return FaqCategory::query()
            ->where('slug', $slug)
            ->whereKeyNot($category->getKey())
            ->exists();
    }
}
