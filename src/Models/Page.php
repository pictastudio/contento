<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PictaStudio\Contento\Events\{PageCreated, PageDeleted, PageUpdated};
use PictaStudio\Contento\Traits\{HasAuthors, HasSlugRouteBinding};
use Spatie\Sluggable\{HasSlug, SlugOptions};

class Page extends Model
{
    use HasAuthors;
    use HasFactory;
    use HasSlug;
    use HasSlugRouteBinding;

    protected $guarded = ['id'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    protected $dispatchesEvents = [
        'created' => PageCreated::class,
        'updated' => PageUpdated::class,
        'deleted' => PageDeleted::class,
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'important' => 'boolean',
            'visible_date_from' => 'datetime',
            'visible_date_to' => 'datetime',
            'published_at' => 'datetime',
            'content' => 'json',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    public function getTable(): string
    {
        return (string) config('contento.table_names.pages', parent::getTable());
    }
}
