<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Http\Requests\StoreFaqRequest;
use PictaStudio\Contento\Http\Resources\FaqResource;
use PictaStudio\Contento\Models\Faq;

class FaqController extends BaseController
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', Faq::class);

        $faqs = Faq::paginate();

        return FaqResource::collection($faqs);
    }

    public function show(Faq $faq): JsonResource
    {
        $this->authorizeIfConfigured('view', $faq);

        return FaqResource::make($faq);
    }

    public function store(StoreFaqRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', Faq::class);

        $faq = Faq::create($request->validated());

        return FaqResource::make($faq);
    }

    public function update(StoreFaqRequest $request, Faq $faq): JsonResource
    {
        $this->authorizeIfConfigured('update', $faq);

        $faq->update($request->validated());

        return FaqResource::make($faq);
    }

    public function destroy(Faq $faq): Response
    {
        $this->authorizeIfConfigured('delete', $faq);

        $faq->delete();

        return response()->noContent();
    }
}
