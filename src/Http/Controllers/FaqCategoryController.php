<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use PictaStudio\Contento\Http\Requests\StoreFaqCategoryRequest;
use PictaStudio\Contento\Http\Resources\FaqCategoryResource;
use PictaStudio\Contento\Models\FaqCategory;
use PictaStudio\Translatable\Locales;
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
        $title = $this->resolveTitle($data);
        $data['title'] = $title;
        $data['slug'] = $this->makeSlug($title);

        $category = FaqCategory::create($data);
        $this->syncTranslations($category, $data);

        return FaqCategoryResource::make($category);
    }

    public function update(StoreFaqCategoryRequest $request, FaqCategory $faqCategory): JsonResource
    {
        $data = $request->validated();
        $title = $this->resolveTitle($data);
        $data['title'] = $title;
        $data['slug'] = $this->makeSlug($title, $faqCategory);

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

    protected function resolveTitle(array $data): string
    {
        if (isset($data['title']) && is_string($data['title'])) {
            return $data['title'];
        }

        foreach ($data as $value) {
            if (is_array($value) && isset($value['title']) && is_string($value['title'])) {
                return $value['title'];
            }
        }

        return '';
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
        $locales = app(Locales::class);

        $applyTranslation = function (string $targetLocale, string $attribute, $value) use ($category, $translationModel): void {
            $translation = $translationModel::query()
                ->where('translatable_type', $category->getMorphClass())
                ->where('translatable_id', $category->getKey())
                ->where('locale', $targetLocale)
                ->where('attribute', $attribute)
                ->first() ?? new $translationModel();

            $translation->setAttribute('translatable_type', $category->getMorphClass());
            $translation->setAttribute('translatable_id', $category->getKey());
            $translation->setAttribute('locale', $targetLocale);
            $translation->setAttribute('attribute', $attribute);
            $translation->setAttribute('value', $value);
            $translation->save();
        };

        if (array_key_exists('title', $data)) {
            $applyTranslation($locale, 'title', $data['title']);
        }

        if (array_key_exists('abstract', $data)) {
            $applyTranslation($locale, 'abstract', $data['abstract']);
        }

        foreach ($data as $key => $value) {
            if (! is_array($value) || ! $locales->has($key)) {
                continue;
            }

            if (array_key_exists('title', $value)) {
                $applyTranslation($key, 'title', $value['title']);
            }

            if (array_key_exists('abstract', $value)) {
                $applyTranslation($key, 'abstract', $value['abstract']);
            }
        }
    }
}
