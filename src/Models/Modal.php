<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PictaStudio\Contento\Traits\{HasAuthors, HasSlugRouteBinding};
use Spatie\Sluggable\{HasSlug, SlugOptions};

class Modal extends Model
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
            'visible_date_from' => 'datetime',
            'visible_date_to' => 'datetime',
            'show_on_all_pages' => 'boolean',
            'timeout' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    public function getTable(): string
    {
        return (string) config('contento.table_names.modals', parent::getTable());
    }
}
