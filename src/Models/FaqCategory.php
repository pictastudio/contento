<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use PictaStudio\Contento\Traits\{HasAuthors, HasSlugRouteBinding};
use PictaStudio\Translatable\Contracts\Translatable as TranslatableContract;
use PictaStudio\Translatable\Translatable;
use Spatie\Sluggable\{HasSlug, SlugOptions};

class FaqCategory extends Model implements TranslatableContract
{
    use HasAuthors;
    use HasFactory;
    use HasSlug;
    use HasSlugRouteBinding;
    use Translatable;

    public array $translatedAttributes = ['title', 'abstract'];

    protected $guarded = ['id'];

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
        static::saving(function (self $model) {
            $model->ensureSlug();
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn (self $model) => (string) $model->title)
            ->saveSlugsTo('slug');
    }

    public function getTable(): string
    {
        return (string) config('contento.table_names.faq_categories', parent::getTable());
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    protected function ensureSlug(): void
    {
        $base = $this->slug ?: Str::slug((string) $this->title);
        $slug = $base;
        $suffix = 1;

        while ($slug === '' || $this->slugExists($slug)) {
            $slug = $base . '-' . $suffix++;
        }

        $this->slug = $slug;
    }

    protected function slugExists(string $slug): bool
    {
        $query = static::query()->where('slug', $slug);

        if ($this->exists) {
            $query->whereKeyNot($this->getKey());
        }

        return $query->exists();
    }
}
