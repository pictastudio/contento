<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Routing\Controller;
use PictaStudio\Contento\Http\Requests\SaveFaqCategoryRequest;
use PictaStudio\Contento\Http\Resources\FaqCategoryResource;
use PictaStudio\Contento\Models\FaqCategory;

class FaqCategoryController extends Controller
{
    public function index()
    {
        $categories = FaqCategory::with('faqs')->paginate();

        return FaqCategoryResource::collection($categories);
    }

    public function show($id)
    {
        $category = FaqCategory::with('faqs')->where('id', $id)->orWhere('slug', $id)->firstOrFail();

        return new FaqCategoryResource($category);
    }

    public function store(SaveFaqCategoryRequest $request)
    {
        $category = FaqCategory::create($request->validated());

        return new FaqCategoryResource($category);
    }

    public function update(SaveFaqCategoryRequest $request, $id)
    {
        $category = FaqCategory::findOrFail($id);
        $category->update($request->validated());

        return new FaqCategoryResource($category);
    }

    public function destroy($id)
    {
        $category = FaqCategory::findOrFail($id);
        $category->delete();

        return response()->noContent();
    }
}
