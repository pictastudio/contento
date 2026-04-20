<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use PictaStudio\Contento\Http\Requests\{IndexFaqCategoryRequest, StoreFaqCategoryRequest};
use PictaStudio\Contento\Http\Resources\FaqCategoryResource;
use PictaStudio\Contento\Models\FaqCategory;

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
            'active' => 'active',
        ]);
        $this->applyTextFilters($categories, $validated, [
            'slug' => 'slug',
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

        $category = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $tagIdsProvided = array_key_exists('tag_ids', $data);
            $tagIds = $data['tag_ids'] ?? [];
            unset($data['tag_ids']);

            $category = query('faq_category')->create($data);

            if ($tagIdsProvided) {
                $category->contentTags()->sync($tagIds ?? []);
            }

            return $category->refresh();
        });

        return FaqCategoryResource::make($category->load('faqs'));
    }

    public function update(StoreFaqCategoryRequest $request, FaqCategory $faqCategory): JsonResource
    {
        $this->authorizeIfConfigured('update', $faqCategory);

        $faqCategory = DB::transaction(function () use ($request, $faqCategory) {
            $data = $request->validated();
            $tagIdsProvided = array_key_exists('tag_ids', $data);
            $tagIds = $data['tag_ids'] ?? [];
            unset($data['tag_ids']);

            $faqCategory->update($data);

            if ($tagIdsProvided) {
                $faqCategory->contentTags()->sync($tagIds ?? []);
            }

            return $faqCategory->refresh();
        });

        return FaqCategoryResource::make($faqCategory->load('faqs'));
    }

    public function destroy(FaqCategory $faqCategory): Response
    {
        $this->authorizeIfConfigured('delete', $faqCategory);

        $faqCategory->delete();

        return response()->noContent();
    }
}
