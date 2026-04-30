<?php

namespace PictaStudio\Contento\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PictaStudio\Contento\Validations\Contracts\ContentTagValidationRules;

use function PictaStudio\Contento\Helpers\Functions\query;

class UpdateMultipleContentTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(ContentTagValidationRules $validationRules): array
    {
        return $validationRules->getBulkUpdateValidationRules();
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $contentTags = $this->input('content_tags', []);
            $incomingParents = [];
            $indexById = [];

            foreach ($contentTags as $index => $contentTag) {
                if (
                    !array_key_exists('parent_id', $contentTag)
                    || !array_key_exists('id', $contentTag)
                ) {
                    continue;
                }

                $id = (int) $contentTag['id'];
                $parentId = $contentTag['parent_id'] === null
                    ? null
                    : (int) $contentTag['parent_id'];

                $incomingParents[$id] = $parentId;
                $indexById[$id] = $index;

                if ($parentId === null) {
                    continue;
                }

                if ((int) $contentTag['parent_id'] === (int) $contentTag['id']) {
                    $validator->errors()->add(
                        "content_tags.{$index}.parent_id",
                        'The parent_id field must be different from id.'
                    );
                }
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $currentParents = query('content_tag')
                ->get()
                ->mapWithKeys(
                    fn ($contentTag): array => [
                        (int) $contentTag->getKey() => $contentTag->parent_id === null
                            ? null
                            : (int) $contentTag->parent_id,
                    ]
                )
                ->all();

            foreach (array_keys($incomingParents) as $contentTagId) {
                if (!$this->createsCircularReference($contentTagId, $incomingParents, $currentParents)) {
                    continue;
                }

                $validator->errors()->add(
                    'content_tags.' . $indexById[$contentTagId] . '.parent_id',
                    'The parent_id field creates a circular reference.'
                );
            }
        });
    }

    private function createsCircularReference(int $startId, array $incomingParents, array $currentParents): bool
    {
        $visited = [];
        $cursor = $startId;

        while ($cursor !== null) {
            if (isset($visited[$cursor])) {
                return true;
            }

            $visited[$cursor] = true;
            $cursor = array_key_exists($cursor, $incomingParents)
                ? $incomingParents[$cursor]
                : ($currentParents[$cursor] ?? null);
        }

        return false;
    }
}
