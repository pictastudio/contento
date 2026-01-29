<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Routing\Controller;
use PictaStudio\Contento\Models\Page;
use PictaStudio\Contento\Http\Resources\PageResource;
use PictaStudio\Contento\Http\Requests\SavePageRequest;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::query()
            ->when(request('type'), fn($q, $type) => $q->where('type', $type))
            ->paginate();

        return PageResource::collection($pages);
    }

    public function show($id)
    {
        $page = Page::where('id', $id)->orWhere('slug', $id)->firstOrFail();
        return new PageResource($page);
    }

    public function store(SavePageRequest $request)
    {
        $page = Page::create($request->validated());
        return new PageResource($page);
    }

    public function update(SavePageRequest $request, $id)
    {
        $page = Page::findOrFail($id);
        $page->update($request->validated());
        return new PageResource($page);
    }

    public function destroy($id)
    {
        $page = Page::findOrFail($id);
        $page->delete();
        return response()->noContent();
    }
}
