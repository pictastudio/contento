<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Http\Requests\SaveFaqCategoryRequest;
use PictaStudio\Contento\Http\Resources\FaqCategoryResource;
use PictaStudio\Contento\Models\FaqCategory;

class FaqCategoryController extends BaseController
{
    public function __construct()
    {
        $this->authorizeResource(FaqCategory::class, 'faq_category');
    }

    public function index(): AnonymousResourceCollection
    {
        $categories = FaqCategory::with('faqs')->paginate();

        return FaqCategoryResource::collection($categories);
    }

    public function show(FaqCategory $faqCategory): JsonResource
    {
        return FaqCategoryResource::make($faqCategory->load('faqs'));
    }

    public function store(SaveFaqCategoryRequest $request): JsonResource
    {
        $category = FaqCategory::create($request->validated());

        return FaqCategoryResource::make($category);
    }

    public function update(SaveFaqCategoryRequest $request, FaqCategory $faqCategory): JsonResource
    {
        $faqCategory->update($request->validated());

        return FaqCategoryResource::make($faqCategory);
    }

    public function destroy(FaqCategory $faqCategory): Response
    {
        $faqCategory->delete();

        return response()->noContent();
    }
}
