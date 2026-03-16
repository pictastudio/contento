<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use PictaStudio\Contento\Actions\MenuItems\{CreateMenuItem, UpdateMenuItem};
use PictaStudio\Contento\Http\Requests\{IndexMenuItemRequest, StoreMenuItemRequest};
use PictaStudio\Contento\Http\Resources\MenuItemResource;
use PictaStudio\Contento\Models\MenuItem;

use function PictaStudio\Contento\Helpers\Functions\{query, resolve_model};

class MenuItemController extends BaseController
{
    public function index(IndexMenuItemRequest $request): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', resolve_model('menu_item'));

        $validated = $request->validated();
        $menuItems = query('menu_item')->with($this->resolveIncludes($validated['include'] ?? []));
        $this->removeImplicitScopesOverriddenByExplicitFilters(
            $menuItems,
            $validated,
            supportsActiveScope: true,
            dateRangeColumns: ['visible_date_from' => 'visible_date_range', 'visible_date_to' => 'visible_date_range']
        );

        $this->applyArrayFilters($menuItems, $validated, [
            'id' => 'id',
        ]);
        $this->applyExactFilters($menuItems, $validated, [
            'menu_id' => 'menu_id',
            'parent_id' => 'parent_id',
            'title' => 'title',
            'slug' => 'slug',
            'link' => 'link',
            'active' => 'active',
        ]);
        $this->applyDateRangeFilters($menuItems, $validated, [
            'visible_date_from' => ['start' => 'visible_date_from_start', 'end' => 'visible_date_from_end'],
            'visible_date_to' => ['start' => 'visible_date_to_start', 'end' => 'visible_date_to_end'],
            'created_at' => ['start' => 'created_at_start', 'end' => 'created_at_end'],
            'updated_at' => ['start' => 'updated_at_start', 'end' => 'updated_at_end'],
        ]);
        $this->applySorting($menuItems, $validated, 'id', 'asc');

        if ($request->boolean('as_tree')) {
            return MenuItemResource::collection(
                $this->buildTree($menuItems->get()->values())
            );
        }

        return MenuItemResource::collection(
            $menuItems->paginate($this->resolvePerPage($validated))
                ->appends($request->query())
        );
    }

    public function show(MenuItem $menuItem, IndexMenuItemRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('view', $menuItem);

        return MenuItemResource::make(
            $menuItem->load($this->resolveIncludes($request->validated('include', [])))
        );
    }

    public function store(StoreMenuItemRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', resolve_model('menu_item'));

        return MenuItemResource::make(
            app(CreateMenuItem::class)->handle($request->validated())
        );
    }

    public function update(StoreMenuItemRequest $request, MenuItem $menuItem): JsonResource
    {
        $this->authorizeIfConfigured('update', $menuItem);

        return MenuItemResource::make(
            app(UpdateMenuItem::class)->handle($menuItem, $request->validated())
        );
    }

    public function destroy(MenuItem $menuItem): Response
    {
        $this->authorizeIfConfigured('delete', $menuItem);

        $menuItem->delete();

        return response()->noContent();
    }

    /**
     * @param  array<int, string>  $includes
     * @return array<int, string>
     */
    private function resolveIncludes(array $includes): array
    {
        $map = [
            'menu' => 'menu',
            'parent' => 'parent',
            'children' => 'children',
        ];

        return collect($includes)
            ->filter(fn (mixed $include): bool => is_string($include))
            ->map(fn (string $include): string => (string) ($map[$include] ?? ''))
            ->filter(fn (string $relation): bool => $relation !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function buildTree(Collection $menuItems): Collection
    {
        $grouped = $menuItems->groupBy(
            fn (MenuItem $menuItem): int => (int) ($menuItem->parent_id ?? 0)
        );

        $attachChildren = function (int $parentId) use (&$attachChildren, $grouped): Collection {
            return ($grouped->get($parentId) ?? collect())
                ->values()
                ->map(function (MenuItem $menuItem) use (&$attachChildren): MenuItem {
                    $menuItem->setRelation('children', $attachChildren((int) $menuItem->getKey()));

                    return $menuItem;
                })
                ->values();
        };

        return $attachChildren(0);
    }
}
