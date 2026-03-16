<?php

namespace PictaStudio\Contento\Actions\MenuItems;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

use function PictaStudio\Contento\Helpers\Functions\query;

class UpdateMenuItem
{
    public function handle(Model $menuItem, array $payload): Model
    {
        $targetMenuId = array_key_exists('menu_id', $payload)
            ? (int) $payload['menu_id']
            : (int) $menuItem->menu_id;
        $targetParentId = array_key_exists('parent_id', $payload)
            ? $payload['parent_id']
            : $menuItem->parent_id;

        $this->guardAgainstInvalidParent($menuItem, $targetMenuId, $targetParentId);

        $menuChanged = $targetMenuId !== (int) $menuItem->menu_id;

        $menuItem->fill($payload);
        $menuItem->save();

        if ($menuChanged) {
            $this->syncDescendantMenuIds($menuItem, $targetMenuId);
        }

        return $menuItem->refresh();
    }

    private function guardAgainstInvalidParent(Model $menuItem, int $targetMenuId, mixed $targetParentId): void
    {
        if (!is_numeric($targetParentId)) {
            return;
        }

        $targetParentId = (int) $targetParentId;

        if ($targetParentId === (int) $menuItem->getKey()) {
            throw ValidationException::withMessages([
                'parent_id' => ['A menu item cannot be its own parent.'],
            ]);
        }

        $parent = query('menu_item')->find($targetParentId);

        if ($parent === null) {
            return;
        }

        if ((int) $parent->menu_id !== $targetMenuId) {
            throw ValidationException::withMessages([
                'parent_id' => ['The selected parent item must belong to the same menu.'],
            ]);
        }

        if ($this->isDescendantOf($menuItem, $targetParentId)) {
            throw ValidationException::withMessages([
                'parent_id' => ['A menu item cannot be moved under one of its descendants.'],
            ]);
        }
    }

    private function isDescendantOf(Model $menuItem, int $candidateParentId): bool
    {
        $children = query('menu_item')
            ->where('parent_id', $menuItem->getKey())
            ->get();

        foreach ($children as $child) {
            if ((int) $child->getKey() === $candidateParentId) {
                return true;
            }

            if ($this->isDescendantOf($child, $candidateParentId)) {
                return true;
            }
        }

        return false;
    }

    private function syncDescendantMenuIds(Model $menuItem, int $targetMenuId): void
    {
        $children = query('menu_item')
            ->where('parent_id', $menuItem->getKey())
            ->get();

        foreach ($children as $child) {
            $child->forceFill(['menu_id' => $targetMenuId])->save();
            $this->syncDescendantMenuIds($child, $targetMenuId);
        }
    }
}
