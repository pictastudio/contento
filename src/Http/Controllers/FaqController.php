<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Http\Requests\StoreFaqRequest;
use PictaStudio\Contento\Http\Resources\FaqResource;
use PictaStudio\Contento\Models\Faq;

class FaqController extends BaseController
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
        return FaqResource::make($faq);
    }

    public function store(StoreFaqRequest $request): JsonResource
    {
        $faq = Faq::create($request->validated());

        return FaqResource::make($faq);
    }

    public function update(StoreFaqRequest $request, Faq $faq): JsonResource
    {
        $faq->update($request->validated());

        return FaqResource::make($faq);
    }

    public function destroy(Faq $faq): Response
    {
        $faq->delete();

        return response()->noContent();
    }
}
