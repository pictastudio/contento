<?php

namespace PictaStudio\Contento\Actions\ContentTags;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

use function PictaStudio\Contento\Helpers\Functions\query;

class UpdateMultipleContentTags
{
    public function handle(array $contentTags): Collection
    {
        return DB::transaction(function () use ($contentTags): Collection {
            $contentTagIds = collect($contentTags)
                ->pluck('id')
                ->map(fn (mixed $id): int => (int) $id)
                ->all();

            $models = query('content_tag')
                ->whereKey($contentTagIds)
                ->get()
                ->keyBy(fn (mixed $contentTag): int => (int) $contentTag->getKey());

            $updatedContentTags = new Collection;
            $updateContentTag = app(UpdateContentTag::class);

            foreach ($contentTags as $contentTagPayload) {
                $contentTag = $models->get((int) $contentTagPayload['id']);

                $updatedContentTags->push(
                    $updateContentTag->handle($contentTag, [
                        'parent_id' => $contentTagPayload['parent_id'],
                        'sort_order' => (int) $contentTagPayload['sort_order'],
                    ])
                );
            }

            return $updatedContentTags;
        });
    }
}
