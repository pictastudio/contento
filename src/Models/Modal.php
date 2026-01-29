<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PictaStudio\Contento\Traits\HasAuthors;

class Modal extends Model
{
    use HasFactory, HasAuthors;

    protected $guarded = ['id'];

    protected $casts = [
        'active' => 'boolean',
        'visible_date_from' => 'datetime',
        'visible_date_to' => 'datetime',
        'show_on_all_pages' => 'boolean',
        'timeout' => 'integer',
    ];

    public function getTable()
    {
        return config('contento.table_names.modals', parent::getTable());
    }
}
