<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Http\Requests\{IndexFaqRequest, StoreFaqRequest};
use PictaStudio\Contento\Http\Resources\FaqResource;
use PictaStudio\Contento\Models\Faq;

class FaqController extends BaseController
{
    public function index(IndexFaqRequest $request): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', Faq::class);

        $validated = $request->validated();
        $faqs = Faq::query();

        $this->applyArrayFilters($faqs, $validated, [
            'id' => 'id',
        ]);
        $this->applyExactFilters($faqs, $validated, [
            'faq_category_id' => 'faq_category_id',
            'slug' => 'slug',
            'active' => 'active',
        ]);
        $this->applyDateRangeFilters($faqs, $validated, [
            'visible_date_from' => ['start' => 'visible_date_from_start', 'end' => 'visible_date_from_end'],
            'visible_date_to' => ['start' => 'visible_date_to_start', 'end' => 'visible_date_to_end'],
            'created_at' => ['start' => 'created_at_start', 'end' => 'created_at_end'],
            'updated_at' => ['start' => 'updated_at_start', 'end' => 'updated_at_end'],
        ]);
        $this->applySorting($faqs, $validated);

        return FaqResource::collection(
            $faqs->paginate($this->resolvePerPage($validated))
                ->appends($request->query())
        );
    }

    public function show(Faq $faq): JsonResource
    {
        $this->authorizeIfConfigured('view', $faq);

        return FaqResource::make($faq);
    }

    public function store(StoreFaqRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', Faq::class);

        $validated = $request->validated();
        $tagIdsProvided = array_key_exists('tag_ids', $validated);
        $tagIds = $validated['tag_ids'] ?? [];
        unset($validated['tag_ids']);

        $faq = Faq::create($validated);

        if ($tagIdsProvided) {
            $faq->contentTags()->sync($tagIds ?? []);
        }

        return FaqResource::make($faq);
    }

    public function update(StoreFaqRequest $request, Faq $faq): JsonResource
    {
        $this->authorizeIfConfigured('update', $faq);

        $validated = $request->validated();
        $tagIdsProvided = array_key_exists('tag_ids', $validated);
        $tagIds = $validated['tag_ids'] ?? [];
        unset($validated['tag_ids']);

        $faq->update($validated);

        if ($tagIdsProvided) {
            $faq->contentTags()->sync($tagIds ?? []);
        }

        return FaqResource::make($faq);
    }

    public function destroy(Faq $faq): Response
    {
        $this->authorizeIfConfigured('delete', $faq);

        $faq->delete();

        return response()->noContent();
    }
}
