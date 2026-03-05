<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, MorphToMany};
use PictaStudio\Contento\Events\{ContentTagCreated, ContentTagDeleted, ContentTagUpdated};
use PictaStudio\Contento\Traits\{EnsuresSlug, HasAuthors, HasContentTags, ResolvesRouteBindingByIdOrSlug, ResolvesSlugSource, SyncsTranslatedSlugs};
use PictaStudio\Translatable\Contracts\Translatable as TranslatableContract;
use PictaStudio\Translatable\Translatable;
use Spatie\Sluggable\{HasSlug, SlugOptions};

class ContentTag extends Model implements TranslatableContract
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

    public array $translatedAttributes = ['name', 'slug', 'abstract', 'description'];

    protected $guarded = ['id'];

    protected $dispatchesEvents = [
        'created' => ContentTagCreated::class,
        'updated' => ContentTagUpdated::class,
        'deleted' => ContentTagDeleted::class,
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'show_in_menu' => 'boolean',
            'in_evidence' => 'boolean',
            'sort_order' => 'integer',
            'visible_from' => 'datetime',
            'visible_until' => 'datetime',
            'metadata' => 'json',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn (self $model): string => $model->resolveSlugSource('name'))
            ->saveSlugsTo('slug');
    }

    public function getTable(): string
    {
        return (string) config('contento.table_names.content_tags', parent::getTable());
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function pages(): MorphToMany
    {
        return $this->morphedByMany(
            Page::class,
            'taggable',
            (string) config('contento.table_names.content_taggables', 'content_taggables'),
            'content_tag_id',
            'taggable_id'
        )->withTimestamps();
    }

    public function faqCategories(): MorphToMany
    {
        return $this->morphedByMany(
            FaqCategory::class,
            'taggable',
            (string) config('contento.table_names.content_taggables', 'content_taggables'),
            'content_tag_id',
            'taggable_id'
        )->withTimestamps();
    }

    public function faqs(): MorphToMany
    {
        return $this->morphedByMany(
            Faq::class,
            'taggable',
            (string) config('contento.table_names.content_taggables', 'content_taggables'),
            'content_tag_id',
            'taggable_id'
        )->withTimestamps();
    }

    protected function translatedSlugSourceAttribute(): string
    {
        return 'name';
    }
}
