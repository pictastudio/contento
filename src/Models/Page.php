<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PictaStudio\Contento\Traits\HasAuthors;

class Page extends Model
{
    use HasFactory, HasAuthors;

    protected $guarded = ['id'];

    protected $casts = [
        'active' => 'boolean',
        'important' => 'boolean',
        'visible_date_from' => 'datetime',
        'visible_date_to' => 'datetime',
        'published_at' => 'datetime',
        'content' => 'array',
    ];

    protected $dispatchesEvents = [
        'created' => \PictaStudio\Contento\Events\PageCreated::class,
        'updated' => \PictaStudio\Contento\Events\PageUpdated::class,
        'deleted' => \PictaStudio\Contento\Events\PageDeleted::class,
    ];

    public function getTable()
    {
        return config('contento.table_names.pages', parent::getTable());
    }
}
