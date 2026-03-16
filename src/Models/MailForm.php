<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PictaStudio\Contento\Traits\{EnsuresSlug, HasAuthors, HasSerializedTranslatableAttributes, ResolvesRouteBindingByIdOrSlug, ResolvesSlugSource, SyncsTranslatedSlugs};
use PictaStudio\Translatable\Contracts\Translatable as TranslatableContract;
use Spatie\Sluggable\{HasSlug, SlugOptions};

class MailForm extends Model implements TranslatableContract
{
    use EnsuresSlug;
    use HasAuthors;
    use HasFactory;
    use HasSerializedTranslatableAttributes;
    use HasSlug;
    use ResolvesRouteBindingByIdOrSlug;
    use ResolvesSlugSource;
    use SyncsTranslatedSlugs;

    public array $translatedAttributes = ['name', 'slug', 'custom_fields', 'redirect_url'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'custom_fields' => 'json',
            'newsletter' => 'boolean',
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
        return (string) config('contento.table_names.mail_forms', parent::getTable());
    }

    protected function translatedSlugSourceAttribute(): string
    {
        return 'name';
    }

    protected function serializedTranslatableAttributes(): array
    {
        return ['custom_fields'];
    }
}
