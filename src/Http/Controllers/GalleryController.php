<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Actions\Galleries\{CreateGallery, UpdateGallery};
use PictaStudio\Contento\Http\Requests\{IndexGalleryRequest, StoreGalleryRequest, UpdateGalleryRequest};
use PictaStudio\Contento\Http\Resources\GalleryResource;
use PictaStudio\Contento\Models\Gallery;

use function PictaStudio\Contento\Helpers\Functions\{query, resolve_model};

class GalleryController extends BaseController
{
    public function index(IndexGalleryRequest $request): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', resolve_model('gallery'));

        $validated = $request->validated();
        $galleries = query('gallery')->with($this->resolveIncludes($validated['include'] ?? []));
        $this->removeImplicitScopesForAllFilter($galleries, $validated, supportsActiveScope: true);
        $this->removeImplicitScopesOverriddenByExplicitFilters($galleries, $validated, supportsActiveScope: true);

        $this->applyArrayFilters($galleries, $validated, [
            'id' => 'id',
        ]);
        $this->applyExactFilters($galleries, $validated, [
            'active' => 'active',
        ]);
        $this->applyTextFilters($galleries, $validated, [
            'title' => 'title',
            'slug' => 'slug',
            'code' => 'code',
        ]);
        $this->applyDateRangeFilters($galleries, $validated, [
            'created_at' => ['start' => 'created_at_start', 'end' => 'created_at_end'],
            'updated_at' => ['start' => 'updated_at_start', 'end' => 'updated_at_end'],
        ]);
        $this->applySorting($galleries, $validated);

        if ($this->requestsAllRecords($validated)) {
            return GalleryResource::collection($galleries->get());
        }

        return GalleryResource::collection(
            $galleries->paginate($this->resolvePerPage($validated))
                ->appends($request->query())
        );
    }

    public function store(StoreGalleryRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', resolve_model('gallery'));

        $gallery = app(CreateGallery::class)->handle($request->validated());

        return GalleryResource::make($gallery);
    }

    public function show(Gallery $gallery, IndexGalleryRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('view', $gallery);

        return GalleryResource::make(
            $gallery->load($this->resolveIncludes($request->validated('include', [])))
        );
    }

    public function update(UpdateGalleryRequest $request, Gallery $gallery): JsonResource
    {
        $this->authorizeIfConfigured('update', $gallery);

        $gallery = app(UpdateGallery::class)->handle($gallery, $request->validated());

        return GalleryResource::make($gallery);
    }

    public function destroy(Gallery $gallery): Response
    {
        $this->authorizeIfConfigured('delete', $gallery);

        $gallery->delete();

        return response()->noContent();
    }

    /**
     * @param  array<int, string>  $includes
     * @return array<int, string>
     */
    private function resolveIncludes(array $includes): array
    {
        return collect($includes)
            ->filter(fn (mixed $include): bool => $include === 'items')
            ->map(fn (): string => 'items')
            ->unique()
            ->values()
            ->all();
    }
}
