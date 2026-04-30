<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PictaStudio\Contento\Events\{CatalogImageCreated, CatalogImageDeleted, CatalogImageUpdated};
use PictaStudio\Contento\Traits\HasAuthors;

class CatalogImage extends Model
{
    use HasAuthors;
    use HasFactory;

    protected $guarded = ['id'];

    protected $dispatchesEvents = [
        'created' => CatalogImageCreated::class,
        'updated' => CatalogImageUpdated::class,
        'deleted' => CatalogImageDeleted::class,
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'json',
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    public function getTable(): string
    {
        return (string) config('contento.table_names.catalog_images', parent::getTable());
    }
}
