<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Http\Requests\{IndexModalRequest, StoreModalRequest};
use PictaStudio\Contento\Http\Resources\ModalResource;
use PictaStudio\Contento\Models\Modal;

class ModalController extends BaseController
{
    public function index(IndexModalRequest $request): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', Modal::class);

        $validated = $request->validated();
        $modals = Modal::query();

        $this->applyArrayFilters($modals, $validated, [
            'id' => 'id',
        ]);
        $this->applyExactFilters($modals, $validated, [
            'slug' => 'slug',
            'template' => 'template',
            'popup_time' => 'popup_time',
            'active' => 'active',
            'show_on_all_pages' => 'show_on_all_pages',
        ]);
        $this->applyNumericRangeFilters($modals, $validated, [
            'timeout' => ['min' => 'timeout_min', 'max' => 'timeout_max'],
        ]);
        $this->applyDateRangeFilters($modals, $validated, [
            'visible_date_from' => ['start' => 'visible_date_from_start', 'end' => 'visible_date_from_end'],
            'visible_date_to' => ['start' => 'visible_date_to_start', 'end' => 'visible_date_to_end'],
            'created_at' => ['start' => 'created_at_start', 'end' => 'created_at_end'],
            'updated_at' => ['start' => 'updated_at_start', 'end' => 'updated_at_end'],
        ]);
        $this->applySorting($modals, $validated);

        return ModalResource::collection(
            $modals->paginate($this->resolvePerPage($validated))
                ->appends($request->query())
        );
    }

    public function show(Modal $modal): JsonResource
    {
        $this->authorizeIfConfigured('view', $modal);

        return ModalResource::make($modal);
    }

    public function store(StoreModalRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', Modal::class);

        $modal = Modal::create($request->validated());

        return ModalResource::make($modal);
    }

    public function update(StoreModalRequest $request, Modal $modal): JsonResource
    {
        $this->authorizeIfConfigured('update', $modal);

        $modal->update($request->validated());

        return ModalResource::make($modal);
    }

    public function destroy(Modal $modal): Response
    {
        $this->authorizeIfConfigured('delete', $modal);

        $modal->delete();

        return response()->noContent();
    }
}
