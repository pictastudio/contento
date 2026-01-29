<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Http\Requests\SaveModalRequest;
use PictaStudio\Contento\Http\Resources\ModalResource;
use PictaStudio\Contento\Models\Modal;

class ModalController extends BaseController
{
    public function __construct()
    {
        $this->authorizeResource(Modal::class, 'modal');
    }

    public function index(): AnonymousResourceCollection
    {
        $modals = Modal::paginate();

        return ModalResource::collection($modals);
    }

    public function show(Modal $modal): JsonResource
    {
        return ModalResource::make($modal);
    }

    public function store(SaveModalRequest $request): JsonResource
    {
        $modal = Modal::create($request->validated());

        return ModalResource::make($modal);
    }

    public function update(SaveModalRequest $request, Modal $modal): JsonResource
    {
        $modal->update($request->validated());

        return ModalResource::make($modal);
    }

    public function destroy(Modal $modal): Response
    {
        $modal->delete();

        return response()->noContent();
    }
}
