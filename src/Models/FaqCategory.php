<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PictaStudio\Contento\Traits\{HasAuthors, HasSlugRouteBinding};
use Spatie\Sluggable\{HasSlug, SlugOptions};

class FaqCategory extends Model
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

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    public function getTable(): string
    {
        return (string) config('contento.table_names.faq_categories', parent::getTable());
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }
}
