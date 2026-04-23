<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Http\Requests\{IndexMetadataRequest, StoreMetadataRequest};
use PictaStudio\Contento\Http\Resources\MetadataResource;
use PictaStudio\Contento\Models\Metadata;

use function PictaStudio\Contento\Helpers\Functions\{query, resolve_model};

class MetadataController extends BaseController
{
    public function index(IndexMetadataRequest $request): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', resolve_model('metadata'));

        $validated = $request->validated();
        $metadata = query('metadata');

        $this->applyArrayFilters($metadata, $validated, [
            'id' => 'id',
        ]);
        $this->applyExactFilters($metadata, $validated, [
            'uri' => 'uri',
        ]);
        $this->applyTextFilters($metadata, $validated, [
            'name' => 'name',
            'slug' => 'slug',
        ]);
        $this->applyDateRangeFilters($metadata, $validated, [
            'created_at' => ['start' => 'created_at_start', 'end' => 'created_at_end'],
            'updated_at' => ['start' => 'updated_at_start', 'end' => 'updated_at_end'],
        ]);
        $this->applySorting($metadata, $validated);

        return MetadataResource::collection(
            $metadata->paginate($this->resolvePerPage($validated))
                ->appends($request->query())
        );
    }

    public function show(Metadata $metadata): JsonResource
    {
        $this->authorizeIfConfigured('view', $metadata);

        return MetadataResource::make($metadata);
    }

    public function store(StoreMetadataRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', resolve_model('metadata'));

        $metadata = query('metadata')->create($request->validated());

        return MetadataResource::make($metadata->refresh());
    }

    public function update(StoreMetadataRequest $request, Metadata $metadata): JsonResource
    {
        $this->authorizeIfConfigured('update', $metadata);

        $validated = $request->validated();

        if (!array_key_exists('slug', $validated) && array_key_exists('name', $validated)) {
            $metadata->slug = null;
        }

        $metadata->update($validated);

        return MetadataResource::make($metadata->refresh());
    }

    public function destroy(Metadata $metadata): Response
    {
        $this->authorizeIfConfigured('delete', $metadata);

        $metadata->delete();

        return response()->noContent();
    }
}
