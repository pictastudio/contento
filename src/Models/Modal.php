<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PictaStudio\Contento\Traits\{HasAuthors, HasSlugRouteBinding};

class Modal extends Model
{
    use HasAuthors;
    use HasFactory;
    use HasSlugRouteBinding;

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

    public function getTable(): string
    {
        return (string) config('contento.table_names.modals', parent::getTable());
    }
}
