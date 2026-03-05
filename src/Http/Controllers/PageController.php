<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Http\Requests\{IndexPageRequest, StorePageRequest};
use PictaStudio\Contento\Http\Resources\PageResource;
use PictaStudio\Contento\Models\Page;

use function PictaStudio\Contento\Helpers\Functions\{query, resolve_model};

class PageController extends BaseController
{
    public function index(IndexPageRequest $request): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', resolve_model('page'));

        $validated = $request->validated();
        $pages = query('page');

        $this->applyArrayFilters($pages, $validated, [
            'id' => 'id',
        ]);
        $this->applyExactFilters($pages, $validated, [
            'slug' => 'slug',
            'type' => 'type',
            'active' => 'active',
            'important' => 'important',
        ]);
        $this->applyDateRangeFilters($pages, $validated, [
            'visible_date_from' => ['start' => 'visible_date_from_start', 'end' => 'visible_date_from_end'],
            'visible_date_to' => ['start' => 'visible_date_to_start', 'end' => 'visible_date_to_end'],
            'published_at' => ['start' => 'published_at_start', 'end' => 'published_at_end'],
            'created_at' => ['start' => 'created_at_start', 'end' => 'created_at_end'],
            'updated_at' => ['start' => 'updated_at_start', 'end' => 'updated_at_end'],
        ]);
        $this->applySorting($pages, $validated);

        return PageResource::collection(
            $pages->paginate($this->resolvePerPage($validated))
                ->appends($request->query())
        );
    }

    public function show(Page $page): JsonResource
    {
        $this->authorizeIfConfigured('view', $page);

        return new PageResource($page);
    }

    public function store(StorePageRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', resolve_model('page'));

        $validated = $request->validated();
        $tagIdsProvided = array_key_exists('tag_ids', $validated);
        $tagIds = $validated['tag_ids'] ?? [];
        unset($validated['tag_ids']);

        $page = query('page')->create($validated);

        if ($tagIdsProvided) {
            $page->contentTags()->sync($tagIds ?? []);
        }

        return new PageResource($page);
    }

    public function update(StorePageRequest $request, Page $page): JsonResource
    {
        $this->authorizeIfConfigured('update', $page);

        $validated = $request->validated();
        $tagIdsProvided = array_key_exists('tag_ids', $validated);
        $tagIds = $validated['tag_ids'] ?? [];
        unset($validated['tag_ids']);

        $page->update($validated);

        if ($tagIdsProvided) {
            $page->contentTags()->sync($tagIds ?? []);
        }

        return new PageResource($page);
    }

    public function destroy(Page $page): Response
    {
        $this->authorizeIfConfigured('delete', $page);

        $page->delete();

        return response()->noContent();
    }
}
