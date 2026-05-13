<?php

namespace PictaStudio\Contento\Actions\MenuItems;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class UpsertMultipleMenuItems
{
    public function handle(array $menuItems): Collection
    {
        return DB::transaction(function () use ($menuItems): Collection {
            $upsertedMenuItemIds = [];
            $menuItemModelClass = resolve_model('menu_item');
            $createMenuItem = app(CreateMenuItem::class);
            $updateMenuItem = app(UpdateMenuItem::class);

            foreach ($menuItems as $menuItemPayload) {
                $menuItemId = array_key_exists('id', $menuItemPayload) && filled($menuItemPayload['id'])
                    ? (int) $menuItemPayload['id']
                    : null;

                if ($menuItemId !== null) {
                    $menuItem = $menuItemModelClass::query()
                        ->withoutGlobalScopes()
                        ->whereKey($menuItemId)
                        ->firstOrFail();

                    $upsertedMenuItem = $updateMenuItem->handle($menuItem, collect($menuItemPayload)->except('id')->all());
                    $upsertedMenuItemIds[] = (int) $upsertedMenuItem->getKey();

                    continue;
                }

                $upsertedMenuItem = $createMenuItem->handle($menuItemPayload);
                $upsertedMenuItemIds[] = (int) $upsertedMenuItem->getKey();
            }

            return $menuItemModelClass::query()
                ->withoutGlobalScopes()
                ->whereKey($upsertedMenuItemIds)
                ->orderBy('menu_id')
                ->orderBy('parent_id')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        });
    }
}
