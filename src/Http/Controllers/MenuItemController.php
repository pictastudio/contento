<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PictaStudio\Contento\Actions\MenuItems\{CreateMenuItem, UpdateMenuItem};
use PictaStudio\Contento\Actions\MenuItems\UpsertMultipleMenuItems;
use PictaStudio\Contento\Actions\Tree\RebuildTreePaths;
use PictaStudio\Contento\Http\Requests\{IndexMenuItemRequest, StoreMenuItemRequest, UpsertMultipleMenuItemRequest};
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
            'active' => 'active',
            'sort_order' => 'sort_order',
        ]);
        $this->applyTextFilters($menuItems, $validated, [
            'title' => 'title',
            'slug' => 'slug',
            'link' => 'link',
        ]);
        $this->applyDateRangeFilters($menuItems, $validated, [
            'visible_date_from' => ['start' => 'visible_date_from_start', 'end' => 'visible_date_from_end'],
            'visible_date_to' => ['start' => 'visible_date_to_start', 'end' => 'visible_date_to_end'],
            'created_at' => ['start' => 'created_at_start', 'end' => 'created_at_end'],
            'updated_at' => ['start' => 'updated_at_start', 'end' => 'updated_at_end'],
        ]);
        if (array_key_exists('sort_by', $validated)) {
            $this->applySorting($menuItems, $validated, 'sort_order', 'asc');
        } else {
            $menuItems
                ->orderBy('menu_id')
                ->orderBy('parent_id')
                ->orderBy('sort_order')
                ->orderBy('id');
        }

        if ($request->boolean('as_tree')) {
            return MenuItemResource::collection(
                $menuItems->get()
                    ->sortBy([
                        ['menu_id', 'asc'],
                        ['sort_order', 'asc'],
                        ['id', 'asc'],
                    ])
                    ->values()
                    ->tree()
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

    public function upsertMultiple(UpsertMultipleMenuItemRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $menuItems = collect($validated['menu_items']);
        $menuItemIds = $menuItems
            ->pluck('id')
            ->filter(fn (mixed $menuItemId): bool => filled($menuItemId))
            ->map(fn (mixed $menuItemId): int => (int) $menuItemId)
            ->unique()
            ->values()
            ->all();

        $existingMenuItems = $request->existingMenuItems();

        if ($existingMenuItems->count() !== count($menuItemIds)) {
            $missingIds = collect($menuItemIds)
                ->diff($existingMenuItems->keys())
                ->values()
                ->all();

            throw ValidationException::withMessages([
                'menu_items' => [
                    'Some menu items are not available for update: ' . implode(', ', $missingIds),
                ],
            ]);
        }

        $needsCreateAuthorization = false;

        foreach ($menuItems as $menuItemPayload) {
            $menuItemId = $menuItemPayload['id'] ?? null;

            if (filled($menuItemId)) {
                $this->authorizeIfConfigured('update', $existingMenuItems->get((int) $menuItemId));

                continue;
            }

            $needsCreateAuthorization = true;
        }

        if ($needsCreateAuthorization) {
            $this->authorizeIfConfigured('create', resolve_model('menu_item'));
        }

        /** @var Collection<int, MenuItem> $upsertedMenuItems */
        $upsertedMenuItems = app(UpsertMultipleMenuItems::class)
            ->handle($menuItems->all());

        return MenuItemResource::collection($upsertedMenuItems);
    }

    public function destroy(MenuItem $menuItem, RebuildTreePaths $treePaths): Response
    {
        $this->authorizeIfConfigured('delete', $menuItem);

        request()->validate([
            'delete_children' => ['boolean'],
        ]);

        $deleteChildren = request()->boolean('delete_children');
        $menuItemIds = $deleteChildren
            ? $treePaths->idsForNodeAndDescendants($menuItem)
            : [$menuItem->getKey()];

        DB::transaction(function () use ($menuItem, $treePaths, $deleteChildren, $menuItemIds): void {
            if (!$deleteChildren) {
                $treePaths->promoteChildren($menuItem);
            }

            resolve_model('menu_item')::withoutGlobalScopes()
                ->whereIn($menuItem->getKeyName(), $menuItemIds)
                ->get()
                ->each
                ->delete();
        });

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
}
