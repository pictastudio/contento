<?php

namespace PictaStudio\Contento\Actions\ContentTags;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

use function PictaStudio\Contento\Helpers\Functions\query;

class CreateContentTag
{
    public function handle(array $payload): Model
    {
        $tagIdsProvided = array_key_exists('tag_ids', $payload);
        $tagIds = Arr::pull($payload, 'tag_ids', []);

        $contentTag = query('content_tag')->create($payload);

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
}
