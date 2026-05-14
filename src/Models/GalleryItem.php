<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PictaStudio\Contento\Events\{GalleryItemCreated, GalleryItemDeleted, GalleryItemUpdated};
use PictaStudio\Contento\Models\Scopes\{Active, InDateRange};
use PictaStudio\Contento\Support\GalleryItemImage;
use PictaStudio\Contento\Traits\HasAuthors;
use PictaStudio\Translatable\Contracts\Translatable as TranslatableContract;
use PictaStudio\Translatable\Translatable;

use function PictaStudio\Contento\Helpers\Functions\resolve_model;

class GalleryItem extends Model implements TranslatableContract
{
    use HasAuthors;
    use HasFactory;
    use Translatable;

    public array $translatedAttributes = ['title', 'subtitle', 'description'];

    protected $guarded = ['id'];

    protected $dispatchesEvents = [
        'created' => GalleryItemCreated::class,
        'updated' => GalleryItemUpdated::class,
        'deleted' => GalleryItemDeleted::class,
    ];

    protected function casts(): array
    {
        return [
            'gallery_id' => 'integer',
            'sort_order' => 'integer',
            'active' => 'boolean',
            'visible_from' => 'datetime',
            'visible_until' => 'datetime',
            'links' => 'json',
            'img' => 'json',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScopes([
            Active::class => new Active,
            'visible_date_range' => new InDateRange('visible_from', 'visible_until'),
        ]);

        static::saving(function (self $galleryItem): void {
            if ($galleryItem->getAttribute('img') === null) {
                return;
            }

            $galleryItem->setAttribute('img', GalleryItemImage::normalize($galleryItem->getAttribute('img')));
        });
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->resolveRouteBindingQuery(
            $this->newQueryWithoutScopes(),
            $value,
            $field ?? $this->getKeyName()
        )->first();
    }

    public function getTable(): string
    {
        return (string) config('contento.table_names.gallery_items', parent::getTable());
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(resolve_model('gallery'), 'gallery_id');
    }
}
