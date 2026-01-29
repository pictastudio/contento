<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Routing\Controller;
use PictaStudio\Contento\Http\Requests\SaveFaqRequest;
use PictaStudio\Contento\Http\Resources\FaqResource;
use PictaStudio\Contento\Models\Faq;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::paginate();

        return FaqResource::collection($faqs);
    }

    public function show($id)
    {
        $faq = Faq::where('id', $id)->orWhere('slug', $id)->firstOrFail();

        return new FaqResource($faq);
    }

    public function store(SaveFaqRequest $request)
    {
        $faq = Faq::create($request->validated());

        return new FaqResource($faq);
    }

    public function update(SaveFaqRequest $request, $id)
    {
        $faq = Faq::findOrFail($id);
        $faq->update($request->validated());

        return new FaqResource($faq);
    }

    public function destroy($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();

        return response()->noContent();
    }
}
