<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Http\Requests\StorePageRequest;
use PictaStudio\Contento\Http\Resources\PageResource;
use PictaStudio\Contento\Models\Page;

class PageController extends BaseController
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', Page::class);

        $pages = Page::query()
            ->when(request('type'), fn ($q, $type) => $q->where('type', $type))
            ->paginate();

        return PageResource::collection($pages);
    }

    public function show(Page $page): JsonResource
    {
        $this->authorizeIfConfigured('view', $page);

        return new PageResource($page);
    }

    public function store(StorePageRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', Page::class);

        $page = Page::create($request->validated());

        return new PageResource($page);
    }

    public function update(StorePageRequest $request, Page $page): JsonResource
    {
        $this->authorizeIfConfigured('update', $page);

        $page->update($request->validated());

        return new PageResource($page);
    }

    public function destroy(Page $page): Response
    {
        $this->authorizeIfConfigured('delete', $page);

        $page->delete();

        return response()->noContent();
    }
}
