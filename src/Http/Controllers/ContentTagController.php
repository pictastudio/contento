<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PictaStudio\Contento\Actions\ContentTags\{CreateContentTag, UpdateContentTag, UpdateMultipleContentTags};
use PictaStudio\Contento\Actions\Tree\RebuildTreePaths;
use PictaStudio\Contento\Http\Requests\{IndexContentTagRequest, StoreContentTagRequest, UpdateContentTagRequest, UpdateMultipleContentTagRequest};
use PictaStudio\Contento\Http\Resources\ContentTagResource;
use PictaStudio\Contento\Models\ContentTag;

use function PictaStudio\Contento\Helpers\Functions\{query, resolve_model};

class ContentTagController extends BaseController
{
    public function index(IndexContentTagRequest $request): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', resolve_model('content_tag'));

        $validated = $request->validated();
        $relations = $this->resolveIncludes($validated['include'] ?? []);
        $contentTags = query('content_tag')->with($relations);
        $this->removeImplicitScopesForAllFilter(
            $contentTags,
            $validated,
            supportsActiveScope: true,
            dateRangeScopes: ['visible_date_range']
        );
        $this->removeImplicitScopesOverriddenByExplicitFilters(
            $contentTags,
            $validated,
            supportsActiveScope: true,
            dateRangeColumns: ['visible_from' => 'visible_date_range', 'visible_until' => 'visible_date_range']
        );

        $this->applyArrayFilters($contentTags, $validated, [
            'id' => 'id',
        ]);
        $this->applyExactFilters($contentTags, $validated, [
            'parent_id' => 'parent_id',
            'active' => 'active',
            'show_in_menu' => 'show_in_menu',
            'in_evidence' => 'in_evidence',
            'sort_order' => 'sort_order',
            'visible_from' => 'visible_from',
            'visible_until' => 'visible_until',
        ]);
        $this->applyTextFilters($contentTags, $validated, [
            'slug' => 'slug',
            'name' => 'name',
        ]);
        $this->applyDateRangeFilters($contentTags, $validated, [
            'visible_from' => ['start' => 'visible_from_start', 'end' => 'visible_from_end'],
            'visible_until' => ['start' => 'visible_until_start', 'end' => 'visible_until_end'],
            'created_at' => ['start' => 'created_at_start', 'end' => 'created_at_end'],
            'updated_at' => ['start' => 'updated_at_start', 'end' => 'updated_at_end'],
        ]);

        if (array_key_exists('sort_by', $validated)) {
            $this->applySorting($contentTags, $validated, 'sort_order', 'asc');
        } else {
            $contentTags
                ->orderBy('parent_id')
                ->orderBy('sort_order')
                ->orderBy('id');
        }

        if ($request->boolean('as_tree')) {
            $tags = $contentTags
                ->get();

            return ContentTagResource::collection(
                $this->buildTree($tags)
            );
        }

        if ($this->requestsAllRecords($validated)) {
            return ContentTagResource::collection($contentTags->get());
        }

        return ContentTagResource::collection(
            $contentTags->paginate($this->resolvePerPage($validated))
                ->appends($request->query())
        );
    }

    public function store(StoreContentTagRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', resolve_model('content_tag'));

        $contentTag = app(CreateContentTag::class)
            ->handle($request->validated());

        return ContentTagResource::make($contentTag);
    }

    public function show(ContentTag $contentTag, IndexContentTagRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('view', $contentTag);

        return ContentTagResource::make(
            $contentTag->load($this->resolveIncludes($request->validated('include', [])))
        );
    }

    public function update(UpdateContentTagRequest $request, ContentTag $contentTag): JsonResource
    {
        $this->authorizeIfConfigured('update', $contentTag);

        $updatedContentTag = app(UpdateContentTag::class)
            ->handle($contentTag, $request->validated());

        return ContentTagResource::make($updatedContentTag);
    }

    public function updateMultiple(UpdateMultipleContentTagRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $contentTagIds = collect($validated['content_tags'])
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        $contentTags = query('content_tag')
            ->whereKey($contentTagIds)
            ->get()
            ->keyBy(fn (mixed $contentTag): int => (int) $contentTag->getKey());

        if ($contentTags->count() !== count($contentTagIds)) {
            $missingIds = collect($contentTagIds)
                ->diff($contentTags->keys())
                ->values()
                ->all();

            throw ValidationException::withMessages([
                'content_tags' => [
                    'Some content tags are not available for update: ' . implode(', ', $missingIds),
                ],
            ]);
        }

        foreach ($contentTagIds as $contentTagId) {
            $this->authorizeIfConfigured('update', $contentTags->get($contentTagId));
        }

        $updatedContentTags = app(UpdateMultipleContentTags::class)
            ->handle($validated['content_tags']);

        return ContentTagResource::collection($updatedContentTags);
    }

    public function destroy(ContentTag $contentTag, RebuildTreePaths $treePaths): Response
    {
        $this->authorizeIfConfigured('delete', $contentTag);

        request()->validate([
            'delete_children' => ['boolean'],
        ]);

        $deleteChildren = request()->boolean('delete_children');
        $contentTagIds = $deleteChildren
            ? $treePaths->idsForNodeAndDescendants($contentTag)
            : [$contentTag->getKey()];

        DB::transaction(function () use ($contentTag, $treePaths, $deleteChildren, $contentTagIds): void {
            DB::table((string) config('contento.table_names.content_taggables', 'content_taggables'))
                ->whereIn('content_tag_id', $contentTagIds)
                ->orWhere(function ($query) use ($contentTag, $contentTagIds): void {
                    $query->where('taggable_type', $contentTag->getMorphClass())
                        ->whereIn('taggable_id', $contentTagIds);
                })
                ->delete();

            if (!$deleteChildren) {
                $treePaths->promoteChildren($contentTag);
            }

            resolve_model('content_tag')::withoutGlobalScopes()
                ->whereIn($contentTag->getKeyName(), $contentTagIds)
                ->get()
                ->each
                ->delete();
        });

        return response()->noContent();
    }

    /**
     * @param  array<int, string>  $includes
     * @return array<int, string>
     */
    private function resolveIncludes(array $includes): array
    {
        $map = [
            'parent' => 'parent',
            'children' => 'children',
            'content_tags' => 'contentTags',
            'pages' => 'pages',
            'faq_categories' => 'faqCategories',
            'faqs' => 'faqs',
        ];

        return collect($includes)
            ->filter(fn (mixed $include): bool => is_string($include))
            ->map(fn (string $include): string => (string) ($map[$include] ?? ''))
            ->filter(fn (string $relation): bool => $relation !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function buildTree(Collection $contentTags): Collection
    {
        $grouped = $contentTags->groupBy(
            fn (ContentTag $contentTag): int => (int) ($contentTag->parent_id ?? 0)
        );

        $attachChildren = function (int $parentId) use (&$attachChildren, $grouped): Collection {
            return ($grouped->get($parentId) ?? collect())
                ->sortBy([
                    ['sort_order', 'asc'],
                    ['id', 'asc'],
                ])
                ->values()
                ->map(function (ContentTag $contentTag) use (&$attachChildren): ContentTag {
                    $contentTag->setRelation('children', $attachChildren((int) $contentTag->getKey()));

                    return $contentTag;
                })
                ->values();
        };

        return $attachChildren(0);
    }
}
