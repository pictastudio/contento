<?php

namespace PictaStudio\Contento\Actions\ContentTags;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PictaStudio\Contento\Actions\Tree\RebuildTreePaths;
use PictaStudio\Contento\Support\CatalogImage;

use function PictaStudio\Contento\Helpers\Functions\query;

class CreateContentTag
{
    public function __construct(
        private readonly RebuildTreePaths $treePaths,
    ) {}

    public function handle(array $payload): Model
    {
        return DB::transaction(function () use ($payload): Model {
            $tagIdsProvided = array_key_exists('tag_ids', $payload);
            $tagIds = Arr::pull($payload, 'tag_ids', []);
            $imagesProvided = array_key_exists('images', $payload);
            $images = Arr::pull($payload, 'images');

            if ($imagesProvided) {
                CatalogImage::validatePayload($images, []);
            }

            $contentTag = query('content_tag')->create($payload);

            if ($imagesProvided) {
                $contentTag->images = CatalogImage::mergeCollection($contentTag, [], $images, 'content_tags');
                $contentTag->save();
            }

            if ($tagIdsProvided) {
                $this->syncTagRelations($contentTag, $tagIds);
            }

            $this->treePaths->rebuild($contentTag);

            return $contentTag->refresh();
        });
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
}
