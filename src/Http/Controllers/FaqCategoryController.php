<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Http\Requests\StoreFaqCategoryRequest;
use PictaStudio\Contento\Http\Resources\FaqCategoryResource;
use PictaStudio\Contento\Models\FaqCategory;
use PictaStudio\Translatable\{Locales, Translation};

class FaqCategoryController extends BaseController
{
    public function __construct()
    {
        $this->authorizeResource(FaqCategory::class, 'faq_category');
    }

    public function index(): AnonymousResourceCollection
    {
        $categories = FaqCategory::with('faqs')->paginate();

        return FaqCategoryResource::collection($categories);
    }

    public function show(FaqCategory $faqCategory): JsonResource
    {
        return FaqCategoryResource::make($faqCategory->load('faqs'));
    }

    public function store(StoreFaqCategoryRequest $request): JsonResource
    {
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
        $data = $request->validated();
        $faqCategory->fill($data);
        $faqCategory->generateSlug();
        $faqCategory->save();
        $this->syncTranslations($faqCategory, $data);

        return FaqCategoryResource::make($faqCategory->load('faqs'));
    }

    public function destroy(FaqCategory $faqCategory): Response
    {
        $faqCategory->delete();

        return response()->noContent();
    }

    protected function syncTranslations(FaqCategory $category, array $data): void
    {
        $locale = app()->getLocale();
        $locales = app(Locales::class);
        $translationModel = config('translatable.translation_model', Translation::class);

        $persist = function (string $targetLocale, string $attribute, mixed $value) use ($category, $translationModel): void {
            $translation = $translationModel::query()
                ->where('translatable_type', $category->getMorphClass())
                ->where('translatable_id', $category->getKey())
                ->where('locale', $targetLocale)
                ->where('attribute', $attribute)
                ->first() ?? new $translationModel;

            $translation->setAttribute('translatable_type', $category->getMorphClass());
            $translation->setAttribute('translatable_id', $category->getKey());
            $translation->setAttribute('locale', $targetLocale);
            $translation->setAttribute('attribute', $attribute);
            $translation->setAttribute('value', $value);
            $translation->save();
        };

        foreach (['title', 'abstract'] as $attribute) {
            if (array_key_exists($attribute, $data)) {
                $persist($locale, $attribute, $data[$attribute]);
            }
        }

        foreach ($data as $targetLocale => $translatedValues) {
            if (!is_array($translatedValues) || !$locales->has($targetLocale)) {
                continue;
            }

            foreach (['title', 'abstract'] as $attribute) {
                if (array_key_exists($attribute, $translatedValues)) {
                    $persist($targetLocale, $attribute, $translatedValues[$attribute]);
                }
            }
        }
    }
}
