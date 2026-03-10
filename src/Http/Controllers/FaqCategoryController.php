<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use PictaStudio\Contento\Http\Requests\{IndexFaqCategoryRequest, StoreFaqCategoryRequest};
use PictaStudio\Contento\Http\Resources\FaqCategoryResource;
use PictaStudio\Contento\Models\FaqCategory;
use PictaStudio\Translatable\{Locales, Translation};

use function PictaStudio\Contento\Helpers\Functions\{query, resolve_model};

class FaqCategoryController extends BaseController
{
    public function index(IndexFaqCategoryRequest $request): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', resolve_model('faq_category'));

        $validated = $request->validated();
        $categories = query('faq_category')->with('faqs');
        $this->removeImplicitScopesOverriddenByExplicitFilters(
            $categories,
            $validated,
            supportsActiveScope: true
        );

        $this->applyArrayFilters($categories, $validated, [
            'id' => 'id',
        ]);
        $this->applyExactFilters($categories, $validated, [
            'slug' => 'slug',
            'active' => 'active',
        ]);
        $this->applyDateRangeFilters($categories, $validated, [
            'created_at' => ['start' => 'created_at_start', 'end' => 'created_at_end'],
            'updated_at' => ['start' => 'updated_at_start', 'end' => 'updated_at_end'],
        ]);
        $this->applySorting($categories, $validated);

        return FaqCategoryResource::collection(
            $categories->paginate($this->resolvePerPage($validated))
                ->appends($request->query())
        );
    }

    public function show(FaqCategory $faqCategory): JsonResource
    {
        $this->authorizeIfConfigured('view', $faqCategory);

        return FaqCategoryResource::make($faqCategory->load('faqs'));
    }

    public function store(StoreFaqCategoryRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', resolve_model('faq_category'));

        $data = $request->validated();
        $tagIdsProvided = array_key_exists('tag_ids', $data);
        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $faqCategoryModelClass = resolve_model('faq_category');
        /** @var FaqCategory $category */
        $category = new $faqCategoryModelClass;
        $category->fill($data);
        $category->generateSlug();
        $category->save();

        if ($tagIdsProvided) {
            $category->contentTags()->sync($tagIds ?? []);
        }

        $this->syncTranslations($category, $data);

        return FaqCategoryResource::make($category->load('faqs'));
    }

    public function update(StoreFaqCategoryRequest $request, FaqCategory $faqCategory): JsonResource
    {
        $this->authorizeIfConfigured('update', $faqCategory);

        $data = $request->validated();
        $tagIdsProvided = array_key_exists('tag_ids', $data);
        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $faqCategory->fill($data);
        $faqCategory->generateSlug();
        $faqCategory->save();

        if ($tagIdsProvided) {
            $faqCategory->contentTags()->sync($tagIds ?? []);
        }

        $this->syncTranslations($faqCategory, $data);

        return FaqCategoryResource::make($faqCategory->load('faqs'));
    }

    public function destroy(FaqCategory $faqCategory): Response
    {
        $this->authorizeIfConfigured('delete', $faqCategory);

        $faqCategory->delete();

        return response()->noContent();
    }

    protected function syncTranslations(Model $category, array $data): void
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
                        $persist(
                            $locale,
                            'slug',
                            $this->makeUniqueLocalizedSlug($category, $translationModel, $locale, $slug, (string) $localeKey)
                        );
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
                            $persist(
                                $targetLocale,
                                'slug',
                                $this->makeUniqueLocalizedSlug(
                                    $category,
                                    $translationModel,
                                    $targetLocale,
                                    $slug,
                                    (string) $localeKey
                                )
                            );
                        }
                    }
                }
            }
        }
    }

    protected function makeUniqueLocalizedSlug(
        Model $category,
        string $translationModel,
        string $locale,
        string $baseSlug,
        string $localeKey
    ): string {
        $slug = $baseSlug;
        $suffix = 1;

        while ($this->localizedSlugExists($category, $translationModel, $locale, $slug, $localeKey)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function localizedSlugExists(
        Model $category,
        string $translationModel,
        string $locale,
        string $slug,
        string $localeKey
    ): bool {
        $query = $translationModel::query()
            ->where('translatable_type', $category->getMorphClass())
            ->where($localeKey, $locale)
            ->where('attribute', 'slug')
            ->where('value', $slug)
            ->where('translatable_id', '!=', $category->getKey());

        if ($query->exists()) {
            return true;
        }

        return query('faq_category')
            ->where('slug', $slug)
            ->whereKeyNot($category->getKey())
            ->exists();
    }
}
