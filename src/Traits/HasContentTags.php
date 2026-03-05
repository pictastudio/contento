<?php

namespace PictaStudio\Contento\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

trait HasContentTags
{
    public function contentTags(): MorphToMany
    {
        return $this->morphToMany(
            resolve_model('content_tag'),
            'taggable',
            (string) config('contento.table_names.content_taggables', 'content_taggables'),
            'taggable_id',
            'content_tag_id'
        )->withTimestamps();
    }
}
