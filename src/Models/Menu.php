<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PictaStudio\Contento\Events\{MenuCreated, MenuDeleted, MenuUpdated};
use PictaStudio\Contento\Models\Scopes\{Active, InDateRange};
use PictaStudio\Contento\Traits\{EnsuresSlug, HasAuthors, ResolvesRouteBindingByIdOrSlug, ResolvesSlugSource, SyncsTranslatedSlugs};
use PictaStudio\Translatable\Contracts\Translatable as TranslatableContract;
use PictaStudio\Translatable\Translatable;
use Spatie\Sluggable\{HasSlug, SlugOptions};

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class Menu extends Model implements TranslatableContract
{
    use EnsuresSlug;
    use HasAuthors;
    use HasFactory;
    use HasSlug;
    use ResolvesRouteBindingByIdOrSlug;
    use ResolvesSlugSource;
    use SyncsTranslatedSlugs;
    use Translatable;

    public array $translatedAttributes = ['title', 'slug'];

    protected $guarded = ['id'];

    protected $dispatchesEvents = [
        'created' => MenuCreated::class,
        'updated' => MenuUpdated::class,
        'deleted' => MenuDeleted::class,
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
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
        return (string) config('contento.table_names.menus', parent::getTable());
    }

    public function items(): HasMany
    {
        return $this->hasMany(resolve_model('menu_item'))
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
