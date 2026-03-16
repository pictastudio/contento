<?php

namespace PictaStudio\Contento\Actions\MenuItems;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

use function PictaStudio\Contento\Helpers\Functions\query;

class CreateMenuItem
{
    public function handle(array $payload): Model
    {
        $this->guardAgainstInvalidParent(
            (int) $payload['menu_id'],
            $payload['parent_id'] ?? null
        );

        return query('menu_item')->create($payload)->refresh();
    }

    private function guardAgainstInvalidParent(int $menuId, mixed $parentId): void
    {
        if (!is_numeric($parentId)) {
            return;
        }

        $parentMenuId = query('menu_item')
            ->whereKey((int) $parentId)
            ->value('menu_id');

        if ($parentMenuId === null || (int) $parentMenuId === $menuId) {
            return;
        }

        throw ValidationException::withMessages([
            'parent_id' => ['The selected parent item must belong to the same menu.'],
        ]);
    }
}
