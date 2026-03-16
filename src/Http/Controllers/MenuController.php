<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Http\Requests\{IndexMenuRequest, StoreMenuRequest};
use PictaStudio\Contento\Http\Resources\MenuResource;
use PictaStudio\Contento\Models\Menu;

use function PictaStudio\Contento\Helpers\Functions\{query, resolve_model};

class MenuController extends BaseController
{
    public function index(IndexMenuRequest $request): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', resolve_model('menu'));

        $validated = $request->validated();
        $menus = query('menu')->with($this->resolveIncludes($validated['include'] ?? []));
        $this->removeImplicitScopesOverriddenByExplicitFilters(
            $menus,
            $validated,
            supportsActiveScope: true,
            dateRangeColumns: ['visible_date_from' => 'visible_date_range', 'visible_date_to' => 'visible_date_range']
        );

        $this->applyArrayFilters($menus, $validated, [
            'id' => 'id',
        ]);
        $this->applyExactFilters($menus, $validated, [
            'title' => 'title',
            'slug' => 'slug',
            'active' => 'active',
        ]);
        $this->applyDateRangeFilters($menus, $validated, [
            'visible_date_from' => ['start' => 'visible_date_from_start', 'end' => 'visible_date_from_end'],
            'visible_date_to' => ['start' => 'visible_date_to_start', 'end' => 'visible_date_to_end'],
            'created_at' => ['start' => 'created_at_start', 'end' => 'created_at_end'],
            'updated_at' => ['start' => 'updated_at_start', 'end' => 'updated_at_end'],
        ]);
        $this->applySorting($menus, $validated);

        return MenuResource::collection(
            $menus->paginate($this->resolvePerPage($validated))
                ->appends($request->query())
        );
    }

    public function show(Menu $menu, IndexMenuRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('view', $menu);

        return MenuResource::make(
            $menu->load($this->resolveIncludes($request->validated('include', [])))
        );
    }

    public function store(StoreMenuRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', resolve_model('menu'));

        return MenuResource::make(
            query('menu')->create($request->validated())
        );
    }

    public function update(StoreMenuRequest $request, Menu $menu): JsonResource
    {
        $this->authorizeIfConfigured('update', $menu);

        $menu->update($request->validated());

        return MenuResource::make($menu);
    }

    public function destroy(Menu $menu): Response
    {
        $this->authorizeIfConfigured('delete', $menu);

        $menu->delete();

        return response()->noContent();
    }

    /**
     * @param  array<int, string>  $includes
     * @return array<int, string>
     */
    private function resolveIncludes(array $includes): array
    {
        $map = [
            'items' => 'items',
        ];

        return collect($includes)
            ->filter(fn (mixed $include): bool => is_string($include))
            ->map(fn (string $include): string => (string) ($map[$include] ?? ''))
            ->filter(fn (string $relation): bool => $relation !== '')
            ->unique()
            ->values()
            ->all();
    }
}
