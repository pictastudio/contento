<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use PictaStudio\Contento\Http\Requests\StoreFaqCategoryRequest;
use PictaStudio\Contento\Http\Resources\FaqCategoryResource;
use PictaStudio\Contento\Models\FaqCategory;
use PictaStudio\Translatable\Translation;

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
        $data['slug'] = $this->makeSlug($data['title']);

        $category = FaqCategory::create($data);
        $this->syncTranslations($category, $data);

        return FaqCategoryResource::make($category);
    }

    public function update(StoreFaqCategoryRequest $request, FaqCategory $faqCategory): JsonResource
    {
        $data = $request->validated();
        $data['slug'] = $this->makeSlug($data['title'], $faqCategory);

        $faqCategory->update($data);
        $this->syncTranslations($faqCategory, $data);

        return FaqCategoryResource::make($faqCategory);
    }

    public function destroy(FaqCategory $faqCategory): Response
    {
        $faqCategory->delete();

        return response()->noContent();
    }

    protected function makeSlug(string $title, ?FaqCategory $ignore = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 1;

        while ($slug === '' || $this->slugExists($slug, $ignore)) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, ?FaqCategory $ignore = null): bool
    {
        $query = FaqCategory::query()->where('slug', $slug);

        if ($ignore) {
            $query->whereKeyNot($ignore->getKey());
        }

        return $query->exists();
    }

    protected function syncTranslations(FaqCategory $category, array $data): void
    {
        $locale = app()->getLocale();
        $translationModel = config('translatable.translation_model', Translation::class);

        if (array_key_exists('title', $data)) {
            $translation = $translationModel::query()
                ->where('translatable_type', $category->getMorphClass())
                ->where('translatable_id', $category->getKey())
                ->where('locale', $locale)
                ->where('attribute', 'title')
                ->first() ?? new $translationModel();

            $translation->setAttribute('translatable_type', $category->getMorphClass());
            $translation->setAttribute('translatable_id', $category->getKey());
            $translation->setAttribute('locale', $locale);
            $translation->setAttribute('attribute', 'title');
            $translation->setAttribute('value', $data['title']);
            $translation->save();
        }

        if (array_key_exists('abstract', $data)) {
            $translation = $translationModel::query()
                ->where('translatable_type', $category->getMorphClass())
                ->where('translatable_id', $category->getKey())
                ->where('locale', $locale)
                ->where('attribute', 'abstract')
                ->first() ?? new $translationModel();

            $translation->setAttribute('translatable_type', $category->getMorphClass());
            $translation->setAttribute('translatable_id', $category->getKey());
            $translation->setAttribute('locale', $locale);
            $translation->setAttribute('attribute', 'abstract');
            $translation->setAttribute('value', $data['abstract']);
            $translation->save();
        }
    }
}
