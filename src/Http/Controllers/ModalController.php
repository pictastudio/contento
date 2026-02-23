<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Http\Requests\StoreModalRequest;
use PictaStudio\Contento\Http\Resources\ModalResource;
use PictaStudio\Contento\Models\Modal;

class ModalController extends BaseController
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', Modal::class);

        $modals = Modal::paginate();

        return ModalResource::collection($modals);
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
