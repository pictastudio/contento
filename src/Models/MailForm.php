<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PictaStudio\Contento\Traits\HasAuthors;

class MailForm extends Model
{
    use HasFactory, HasAuthors;

    protected $guarded = ['id'];

    protected $casts = [
        'custom_fields' => 'array',
        'newsletter' => 'boolean',
    ];

    public function getTable()
    {
        return config('contento.table_names.mail_forms', parent::getTable());
    }
}
