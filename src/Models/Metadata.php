<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PictaStudio\Contento\Events\{MetadataCreated, MetadataDeleted, MetadataUpdated};
use PictaStudio\Contento\Traits\{EnsuresSlug, ResolvesRouteBindingByIdOrSlug};
use Spatie\Sluggable\{HasSlug, SlugOptions};

class Metadata extends Model
{
    use EnsuresSlug;
    use HasFactory;
    use HasSlug;
    use ResolvesRouteBindingByIdOrSlug;

    protected $guarded = ['id'];

    protected $dispatchesEvents = [
        'created' => MetadataCreated::class,
        'updated' => MetadataUpdated::class,
        'deleted' => MetadataDeleted::class,
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'json',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->preventOverwrite();
    }

    public function getTable(): string
    {
        return (string) config('contento.table_names.metadata', parent::getTable());
    }
}
