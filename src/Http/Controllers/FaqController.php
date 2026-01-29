<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use PictaStudio\Contento\Http\Requests\SaveFaqRequest;
use PictaStudio\Contento\Http\Resources\FaqResource;
use PictaStudio\Contento\Models\Faq;

class FaqController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Faq::class, 'faq');
    }

    public function index(): AnonymousResourceCollection
    {
        $faqs = Faq::paginate();

        return FaqResource::collection($faqs);
    }

    public function show(Faq $faq): JsonResource
    {
        return new FaqResource($faq);
    }

    public function store(SaveFaqRequest $request): JsonResource
    {
        $faq = Faq::create($request->validated());

        return new FaqResource($faq);
    }

    public function update(SaveFaqRequest $request, Faq $faq): JsonResource
    {
        $faq->update($request->validated());

        return new FaqResource($faq);
    }

    public function destroy(Faq $faq): Response
    {
        $faq->delete();

        return response()->noContent();
    }
}
