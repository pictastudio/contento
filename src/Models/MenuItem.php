<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Nevadskiy\Tree\AsTree;
use PictaStudio\Contento\Events\{MenuItemCreated, MenuItemDeleted, MenuItemUpdated};
use PictaStudio\Contento\Models\Scopes\{Active, InDateRange};
use PictaStudio\Contento\Traits\{EnsuresSlug, HasAuthors, ResolvesRouteBindingByIdOrSlug, ResolvesSlugSource, SyncsTranslatedSlugs};
use PictaStudio\Translatable\Contracts\Translatable as TranslatableContract;
use PictaStudio\Translatable\Translatable;
use Spatie\Sluggable\{HasSlug, SlugOptions};

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class MenuItem extends Model implements TranslatableContract
{
    use AsTree;
    use EnsuresSlug;
    use HasAuthors;
    use HasFactory;
    use HasSlug;
    use ResolvesRouteBindingByIdOrSlug;
    use ResolvesSlugSource;
    use SyncsTranslatedSlugs;
    use Translatable;

    public array $translatedAttributes = ['title', 'slug', 'link'];

    protected $guarded = ['id'];

    protected $dispatchesEvents = [
        'created' => MenuItemCreated::class,
        'updated' => MenuItemUpdated::class,
        'deleted' => MenuItemDeleted::class,
    ];

    protected function casts(): array
    {
        return [
            'menu_id' => 'integer',
            'parent_id' => 'integer',
            'active' => 'boolean',
            'sort_order' => 'integer',
            'visible_date_from' => 'datetime',
            'visible_date_to' => 'datetime',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScopes([
            Active::class => new Active,
            'visible_date_range' => new InDateRange('visible_date_from', 'visible_date_to'),
        ]);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn (self $model): string => $model->resolveSlugSource('title'))
            ->saveSlugsTo('slug');
    }

    public function getTable(): string
    {
        return (string) config('contento.table_names.menu_items', parent::getTable());
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(resolve_model('menu'), 'menu_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(resolve_model('menu_item'), 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
