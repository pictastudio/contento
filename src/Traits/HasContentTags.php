<?php

namespace PictaStudio\Contento\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use PictaStudio\Contento\Models\ContentTag;

trait HasContentTags
{
    public function contentTags(): MorphToMany
    {
        return $this->morphToMany(
            ContentTag::class,
            'taggable',
            (string) config('contento.table_names.content_taggables', 'content_taggables'),
            'taggable_id',
            'content_tag_id'
        )->withTimestamps();
    }
}
