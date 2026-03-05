<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use PictaStudio\Contento\Actions\ContentTags\{CreateContentTag, UpdateContentTag};
use PictaStudio\Contento\Http\Requests\{IndexContentTagRequest, StoreContentTagRequest, UpdateContentTagRequest};
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

        $this->applyArrayFilters($contentTags, $validated, [
            'id' => 'id',
        ]);
        $this->applyExactFilters($contentTags, $validated, [
            'parent_id' => 'parent_id',
            'slug' => 'slug',
            'name' => 'name',
            'active' => 'active',
            'show_in_menu' => 'show_in_menu',
            'in_evidence' => 'in_evidence',
            'sort_order' => 'sort_order',
        ]);
        $this->applyDateRangeFilters($contentTags, $validated, [
            'visible_from' => ['start' => 'visible_from_start', 'end' => 'visible_from_end'],
            'visible_until' => ['start' => 'visible_until_start', 'end' => 'visible_until_end'],
            'created_at' => ['start' => 'created_at_start', 'end' => 'created_at_end'],
            'updated_at' => ['start' => 'updated_at_start', 'end' => 'updated_at_end'],
        ]);

        $this->applySorting($contentTags, $validated, 'sort_order', 'asc');

        if ($request->boolean('as_tree')) {
            $tags = $contentTags
                ->get()
                ->sortBy([
                    ['sort_order', 'asc'],
                    ['id', 'asc'],
                ])
                ->values();

            return ContentTagResource::collection(
                $this->buildTree($tags)
            );
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

    public function destroy(ContentTag $contentTag): Response
    {
        $this->authorizeIfConfigured('delete', $contentTag);

        $contentTag->delete();

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
