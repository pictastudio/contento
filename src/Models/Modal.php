<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PictaStudio\Contento\Traits\{EnsuresSlug, HasAuthors, ResolvesRouteBindingByIdOrSlug, ResolvesSlugSource, SyncsTranslatedSlugs};
use PictaStudio\Translatable\Contracts\Translatable as TranslatableContract;
use PictaStudio\Translatable\Translatable;
use Spatie\Sluggable\{HasSlug, SlugOptions};

class Modal extends Model implements TranslatableContract
{
    use EnsuresSlug;
    use HasAuthors;
    use HasFactory;
    use HasSlug;
    use ResolvesRouteBindingByIdOrSlug;
    use ResolvesSlugSource;
    use SyncsTranslatedSlugs;
    use Translatable;

    public array $translatedAttributes = ['title', 'content', 'cta_button_text', 'slug'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'visible_date_from' => 'datetime',
            'visible_date_to' => 'datetime',
            'show_on_all_pages' => 'boolean',
            'timeout' => 'integer',
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
        return (string) config('contento.table_names.modals', parent::getTable());
    }
}
