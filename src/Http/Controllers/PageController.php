<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
        $pages = query('page')->with($this->resolveIncludes($validated['include'] ?? []));
        $this->removeImplicitScopesOverriddenByExplicitFilters(
            $pages,
            $validated,
            supportsActiveScope: true,
            dateRangeColumns: ['visible_date_from' => 'visible_date_range', 'visible_date_to' => 'visible_date_range'],
            supportsPublishedScope: true
        );

        $this->applyArrayFilters($pages, $validated, [
            'id' => 'id',
        ]);
        $this->applyExactFilters($pages, $validated, [
            'active' => 'active',
            'important' => 'important',
        ]);
        $this->applyTextFilters($pages, $validated, [
            'slug' => 'slug',
            'type' => 'type',
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

    public function show(Page $page, IndexPageRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('view', $page);

        return new PageResource(
            $page->load($this->resolveIncludes($request->validated('include', [])))
        );
    }

    public function store(StorePageRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', resolve_model('page'));

        $page = DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $tagIdsProvided = array_key_exists('tag_ids', $validated);
            $tagIds = $validated['tag_ids'] ?? [];
            unset($validated['tag_ids']);

            $page = query('page')->create($validated);

            if ($tagIdsProvided) {
                $page->contentTags()->sync($tagIds ?? []);
            }

            return $page->refresh();
        });

        return new PageResource($page);
    }

    public function update(StorePageRequest $request, Page $page): JsonResource
    {
        $this->authorizeIfConfigured('update', $page);

        $page = DB::transaction(function () use ($request, $page) {
            $validated = $request->validated();
            $tagIdsProvided = array_key_exists('tag_ids', $validated);
            $tagIds = $validated['tag_ids'] ?? [];
            unset($validated['tag_ids']);

            $page->update($validated);

            if ($tagIdsProvided) {
                $page->contentTags()->sync($tagIds ?? []);
            }

            return $page->refresh();
        });

        return new PageResource($page);
    }

    public function destroy(Page $page): Response
    {
        $this->authorizeIfConfigured('delete', $page);

        $page->delete();

        return response()->noContent();
    }

    /**
     * @param  array<int, string>  $includes
     * @return array<int, string>
     */
    private function resolveIncludes(array $includes): array
    {
        $map = [
            'content_tags' => 'contentTags',
        ];

        return collect($includes)
            ->filter(fn (mixed $include): bool => is_string($include))
            ->map(fn (string $include): string => (string) ($map[$include] ?? ''))
            ->filter(fn (string $relation): bool => $relation !== '')
            ->unique()
            ->values()
            ->all();
    }
}
