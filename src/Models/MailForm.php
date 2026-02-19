<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PictaStudio\Contento\Traits\{EnsuresSlug, HasAuthors, HasSlugRouteBinding, ResolvesSlugSource};
use Spatie\Sluggable\{HasSlug, SlugOptions};

class MailForm extends Model
{
    use HasAuthors;
    use EnsuresSlug;
    use HasFactory;
    use HasSlug;
    use HasSlugRouteBinding;
    use ResolvesSlugSource;

    protected $guarded = ['id'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn (self $model): string => $model->resolveSlugSource('name'))
            ->saveSlugsTo('slug');
    }

    protected function casts(): array
    {
        return [
            'custom_fields' => 'json',
            'newsletter' => 'boolean',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    public function getTable(): string
    {
        return (string) config('contento.table_names.mail_forms', parent::getTable());
    }
}
