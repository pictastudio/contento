<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Routing\Controller;
use PictaStudio\Contento\Http\Requests\SaveModalRequest;
use PictaStudio\Contento\Http\Resources\ModalResource;
use PictaStudio\Contento\Models\Modal;

class ModalController extends Controller
{
    public function index()
    {
        $modals = Modal::paginate();

        return ModalResource::collection($modals);
    }

    public function show($id)
    {
        $modal = Modal::where('id', $id)->orWhere('slug', $id)->firstOrFail();

        return new ModalResource($modal);
    }

    public function store(SaveModalRequest $request)
    {
        $modal = Modal::create($request->validated());

        return new ModalResource($modal);
    }

    public function update(SaveModalRequest $request, $id)
    {
        $modal = Modal::findOrFail($id);
        $modal->update($request->validated());

        return new ModalResource($modal);
    }

    public function destroy($id)
    {
        $modal = Modal::findOrFail($id);
        $modal->delete();

        return response()->noContent();
    }
}
