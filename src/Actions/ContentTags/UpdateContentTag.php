<?php

namespace PictaStudio\Contento\Actions\ContentTags;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PictaStudio\Contento\Actions\Tree\RebuildTreePaths;
use PictaStudio\Contento\Support\CatalogImage;

use function PictaStudio\Contento\Helpers\Functions\query;

class UpdateContentTag
{
    public function __construct(
        private readonly RebuildTreePaths $treePaths,
    ) {}

    public function handle(Model $contentTag, array $payload): Model
    {
        return DB::transaction(function () use ($contentTag, $payload): Model {
            $tagIdsProvided = array_key_exists('tag_ids', $payload);
            $tagIds = Arr::pull($payload, 'tag_ids', []);
            $imagesProvided = array_key_exists('images', $payload);
            $images = Arr::pull($payload, 'images');
            $parentId = array_key_exists('parent_id', $payload)
                ? $payload['parent_id']
                : $contentTag->parent_id;

            $this->guardAgainstInvalidParent($contentTag, $parentId);
            $this->guardAgainstInvalidTagRelations($contentTag, $tagIds);

            if ($imagesProvided) {
                $currentImages = CatalogImage::normalizeCollection($contentTag->getAttribute('images'));
                CatalogImage::validatePayload($images, CatalogImage::collectUsedIds($currentImages), 'images', $currentImages);
                $payload['images'] = CatalogImage::mergeCollection($contentTag, $currentImages, $images, 'content_tags');
            }

            $contentTag->fill($payload);
            $contentTag->save();

            if ($tagIdsProvided) {
                $this->syncTagRelations($contentTag, $tagIds);
            }

            $this->treePaths->rebuild($contentTag);

            return $contentTag->refresh();
        });
    }

    private function guardAgainstInvalidTagRelations(Model $contentTag, mixed $tagIds): void
    {
        $tagIdsCollection = collect($tagIds ?? [])
            ->map(fn (mixed $id): int => (int) $id);

        if ($tagIdsCollection->contains((int) $contentTag->getKey())) {
            throw ValidationException::withMessages([
                'tag_ids' => ['A content tag cannot be attached to itself.'],
            ]);
        }
    }

    private function syncTagRelations(Model $contentTag, mixed $tagIds): void
    {
        $contentTag->contentTags()->sync(
            collect($tagIds ?? [])
                ->map(fn (mixed $id): int => (int) $id)
                ->all()
        );
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
