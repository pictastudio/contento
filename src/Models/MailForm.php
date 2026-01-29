<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PictaStudio\Contento\Traits\{HasAuthors, HasSlugRouteBinding};

class MailForm extends Model
{
    use HasAuthors;
    use HasFactory;
    use HasSlugRouteBinding;

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

    public function getTable(): string
    {
        return (string) config('contento.table_names.mail_forms', parent::getTable());
    }
}
