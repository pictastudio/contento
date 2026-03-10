<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PictaStudio\Contento\Events\{PageCreated, PageDeleted, PageUpdated};
use PictaStudio\Contento\Models\Scopes\{Active, InDateRange, Published};
use PictaStudio\Contento\Traits\{EnsuresSlug, HasAuthors, HasContentTags, ResolvesRouteBindingByIdOrSlug, ResolvesSlugSource, SyncsTranslatedSlugs};
use PictaStudio\Translatable\Contracts\Translatable as TranslatableContract;
use PictaStudio\Translatable\Translatable;
use Spatie\Sluggable\{HasSlug, SlugOptions};

class Page extends Model implements TranslatableContract
{
    use EnsuresSlug;
    use HasAuthors;
    use HasContentTags;
    use HasFactory;
    use HasSlug;
    use ResolvesRouteBindingByIdOrSlug;
    use ResolvesSlugSource;
    use SyncsTranslatedSlugs;
    use Translatable;

    public array $translatedAttributes = ['title', 'abstract', 'slug'];

    protected $guarded = ['id'];

    protected $dispatchesEvents = [
        'created' => PageCreated::class,
        'updated' => PageUpdated::class,
        'deleted' => PageDeleted::class,
    ];

    protected static function booted(): void
    {
        static::addGlobalScopes([
            Active::class => new Active,
            'visible_date_range' => new InDateRange('visible_date_from', 'visible_date_to'),
            Published::class => new Published('published_at'),
        ]);
    }

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

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn (self $model): string => $model->resolveSlugSource('title'))
            ->saveSlugsTo('slug');
    }

    public function getTable(): string
    {
        return (string) config('contento.table_names.pages', parent::getTable());
    }
}
