<?php

namespace PictaStudio\Contento\Actions\ContentTags;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use PictaStudio\Contento\Models\ContentTag;

class CreateContentTag
{
    public function handle(array $payload): ContentTag
    {
        $tagIdsProvided = array_key_exists('tag_ids', $payload);
        $tagIds = Arr::pull($payload, 'tag_ids', []);

        $contentTag = ContentTag::create($payload);

        if ($tagIdsProvided) {
            $this->syncTagRelations($contentTag, $tagIds);
        }

        return $contentTag->refresh();
    }

    private function syncTagRelations(ContentTag $contentTag, mixed $tagIds): void
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
