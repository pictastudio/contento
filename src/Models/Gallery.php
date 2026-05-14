<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\HasMany;
use PictaStudio\Contento\Events\{GalleryCreated, GalleryDeleted, GalleryUpdated};
use PictaStudio\Contento\Models\Scopes\Active;
use PictaStudio\Contento\Traits\{EnsuresSlug, HasAuthors, ResolvesRouteBindingByIdOrSlug, ResolvesSlugSource, SyncsTranslatedSlugs};
use PictaStudio\Translatable\Contracts\Translatable as TranslatableContract;
use PictaStudio\Translatable\Translatable;
use Spatie\Sluggable\{HasSlug, SlugOptions};

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class Gallery extends Model implements TranslatableContract
{
    use EnsuresSlug;
    use HasAuthors;
    use HasFactory;
    use HasSlug;
    use ResolvesRouteBindingByIdOrSlug {
        resolveRouteBinding as resolveRouteBindingByIdOrSlug;
    }
    use ResolvesSlugSource;
    use SyncsTranslatedSlugs;
    use Translatable;

    public array $translatedAttributes = ['title', 'slug', 'abstract'];

    protected $guarded = ['id'];

    protected $dispatchesEvents = [
        'created' => GalleryCreated::class,
        'updated' => GalleryUpdated::class,
        'deleted' => GalleryDeleted::class,
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(Active::class, new Active);
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $model = $this->resolveRouteBindingByIdOrSlug($value, $field);

        if ($model !== null || $field !== null) {
            return $model;
        }

        return $this->resolveRouteBindingQuery(
            $this->newQueryWithoutScopes(),
            $value,
            'code'
        )->first();
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn (self $model): string => $model->resolveSlugSource('title'))
            ->saveSlugsTo('slug');
    }

    public function getTable(): string
    {
        return (string) config('contento.table_names.galleries', parent::getTable());
    }

    public function items(): HasMany
    {
        return $this->hasMany(resolve_model('gallery_item'), 'gallery_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
