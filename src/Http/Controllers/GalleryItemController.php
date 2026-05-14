<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Actions\GalleryItems\{CreateGalleryItem, UpdateGalleryItem};
use PictaStudio\Contento\Http\Requests\{IndexGalleryItemRequest, StoreGalleryItemRequest, UpdateGalleryItemRequest};
use PictaStudio\Contento\Http\Resources\GalleryItemResource;
use PictaStudio\Contento\Models\GalleryItem;

use function PictaStudio\Contento\Helpers\Functions\{query, resolve_model};

class GalleryItemController extends BaseController
{
    public function index(IndexGalleryItemRequest $request): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', resolve_model('gallery_item'));

        $validated = $request->validated();
        $galleryItems = query('gallery_item')->with($this->resolveIncludes($validated['include'] ?? []));
        $this->removeImplicitScopesForAllFilter(
            $galleryItems,
            $validated,
            supportsActiveScope: true,
            dateRangeScopes: ['visible_date_range']
        );
        $this->removeImplicitScopesOverriddenByExplicitFilters(
            $galleryItems,
            $validated,
            supportsActiveScope: true,
            dateRangeColumns: ['visible_from' => 'visible_date_range', 'visible_until' => 'visible_date_range']
        );

        $this->applyArrayFilters($galleryItems, $validated, [
            'id' => 'id',
        ]);
        $this->applyExactFilters($galleryItems, $validated, [
            'gallery_id' => 'gallery_id',
            'active' => 'active',
            'sort_order' => 'sort_order',
            'visible_from' => 'visible_from',
            'visible_until' => 'visible_until',
        ]);
        $this->applyTextFilters($galleryItems, $validated, [
            'title' => 'title',
        ]);
        $this->applyDateRangeFilters($galleryItems, $validated, [
            'visible_from' => ['start' => 'visible_from_start', 'end' => 'visible_from_end'],
            'visible_until' => ['start' => 'visible_until_start', 'end' => 'visible_until_end'],
            'created_at' => ['start' => 'created_at_start', 'end' => 'created_at_end'],
            'updated_at' => ['start' => 'updated_at_start', 'end' => 'updated_at_end'],
        ]);

        if (array_key_exists('sort_by', $validated)) {
            $this->applySorting($galleryItems, $validated, 'sort_order', 'asc');
        } else {
            $galleryItems
                ->orderBy('sort_order')
                ->orderBy('id');
        }

        if ($this->requestsAllRecords($validated)) {
            return GalleryItemResource::collection($galleryItems->get());
        }

        return GalleryItemResource::collection(
            $galleryItems->paginate($this->resolvePerPage($validated))
                ->appends($request->query())
        );
    }

    public function store(StoreGalleryItemRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', resolve_model('gallery_item'));

        $galleryItem = app(CreateGalleryItem::class)->handle($request->validated());

        return GalleryItemResource::make($galleryItem);
    }

    public function show(GalleryItem $galleryItem, IndexGalleryItemRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('view', $galleryItem);

        return GalleryItemResource::make(
            $galleryItem->load($this->resolveIncludes($request->validated('include', [])))
        );
    }

    public function update(UpdateGalleryItemRequest $request, GalleryItem $galleryItem): JsonResource
    {
        $this->authorizeIfConfigured('update', $galleryItem);

        $galleryItem = app(UpdateGalleryItem::class)->handle($galleryItem, $request->validated());

        return GalleryItemResource::make($galleryItem);
    }

    public function destroy(GalleryItem $galleryItem): Response
    {
        $this->authorizeIfConfigured('delete', $galleryItem);

        $galleryItem->delete();

        return response()->noContent();
    }

    /**
     * @param  array<int, string>  $includes
     * @return array<int, string>
     */
    private function resolveIncludes(array $includes): array
    {
        return collect($includes)
            ->filter(fn (mixed $include): bool => $include === 'gallery')
            ->map(fn (): string => 'gallery')
            ->unique()
            ->values()
            ->all();
    }
}
