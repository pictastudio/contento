<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Actions\CatalogImages\{CreateCatalogImage, DeleteCatalogImage, UpdateCatalogImage};
use PictaStudio\Contento\Http\Requests\{IndexCatalogImageRequest, StoreCatalogImageRequest, UpdateCatalogImageRequest};
use PictaStudio\Contento\Http\Resources\CatalogImageResource;

use function PictaStudio\Contento\Helpers\Functions\{query, resolve_model};

class CatalogImageController extends BaseController
{
    public function index(IndexCatalogImageRequest $request): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', resolve_model('catalog_image'));

        $validated = $request->validated();
        $catalogImages = query('catalog_image');

        $this->applyArrayFilters($catalogImages, $validated, [
            'id' => 'id',
        ]);
        $this->applyTextFilters($catalogImages, $validated, [
            'name' => 'name',
            'title' => 'title',
            'alt' => 'alt',
            'mime_type' => 'mime_type',
        ]);
        $this->applyNumericRangeFilters($catalogImages, $validated, [
            'size' => ['min' => 'size_min', 'max' => 'size_max'],
        ]);
        $this->applyDateRangeFilters($catalogImages, $validated, [
            'created_at' => ['start' => 'created_at_start', 'end' => 'created_at_end'],
            'updated_at' => ['start' => 'updated_at_start', 'end' => 'updated_at_end'],
        ]);
        $this->applySorting($catalogImages, $validated);

        if ($this->requestsAllRecords($validated)) {
            return CatalogImageResource::collection($catalogImages->get());
        }

        return CatalogImageResource::collection(
            $catalogImages->paginate($this->resolvePerPage($validated))
                ->appends($request->query())
        );
    }

    public function store(StoreCatalogImageRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', resolve_model('catalog_image'));

        $catalogImage = app(CreateCatalogImage::class)
            ->handle($request->validated());

        return CatalogImageResource::make($catalogImage);
    }

    public function show(Model $catalogImage): JsonResource
    {
        $this->authorizeIfConfigured('view', $catalogImage);

        return CatalogImageResource::make($catalogImage);
    }

    public function update(UpdateCatalogImageRequest $request, Model $catalogImage): JsonResource
    {
        $this->authorizeIfConfigured('update', $catalogImage);

        $updatedCatalogImage = app(UpdateCatalogImage::class)
            ->handle($catalogImage, $request->validated());

        return CatalogImageResource::make($updatedCatalogImage);
    }

    public function destroy(Model $catalogImage): Response
    {
        $this->authorizeIfConfigured('delete', $catalogImage);

        app(DeleteCatalogImage::class)->handle($catalogImage);

        return response()->noContent();
    }
}
