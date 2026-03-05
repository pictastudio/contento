<?php

namespace PictaStudio\Contento\Actions\ContentTags;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

use function PictaStudio\Contento\Helpers\Functions\query;

class UpdateContentTag
{
    public function handle(Model $contentTag, array $payload): Model
    {
        $tagIdsProvided = array_key_exists('tag_ids', $payload);
        $tagIds = Arr::pull($payload, 'tag_ids', []);
        $parentId = array_key_exists('parent_id', $payload)
            ? $payload['parent_id']
            : $contentTag->parent_id;

        $this->guardAgainstInvalidParent($contentTag, $parentId);

        $contentTag->fill($payload);
        $contentTag->save();

        if ($tagIdsProvided) {
            $this->syncTagRelations($contentTag, $tagIds);
        }

        return $contentTag->refresh();
    }

    private function syncTagRelations(Model $contentTag, mixed $tagIds): void
    {
        $tagIdsCollection = collect($tagIds ?? [])
            ->map(fn (mixed $id): int => (int) $id);

        if ($tagIdsCollection->contains((int) $contentTag->getKey())) {
            throw ValidationException::withMessages([
                'tag_ids' => ['A content tag cannot be attached to itself.'],
            ]);
        }

        $contentTag->contentTags()->sync($tagIdsCollection->all());
    }

    private function guardAgainstInvalidParent(Model $contentTag, mixed $parentId): void
    {
        if (!is_numeric($parentId)) {
            return;
        }

        $parentId = (int) $parentId;

        if ($parentId === (int) $contentTag->getKey()) {
            throw ValidationException::withMessages([
                'parent_id' => ['A content tag cannot be its own parent.'],
            ]);
        }

        if ($this->isDescendantOf($contentTag, $parentId)) {
            throw ValidationException::withMessages([
                'parent_id' => ['A content tag cannot be moved under one of its descendants.'],
            ]);
        }
    }

    private function isDescendantOf(Model $contentTag, int $candidateParentId): bool
    {
        $children = query('content_tag')
            ->where('parent_id', $contentTag->getKey())
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
}
