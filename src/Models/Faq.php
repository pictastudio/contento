<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PictaStudio\Contento\Traits\HasAuthors;

class Faq extends Model
{
    use HasFactory, HasAuthors;

    protected $guarded = ['id'];

    protected $casts = [
        'active' => 'boolean',
        'visible_date_from' => 'datetime',
        'visible_date_to' => 'datetime',
    ];

    public function getTable()
    {
        return config('contento.table_names.faqs', parent::getTable());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FaqCategory::class, 'faq_category_id');
    }
}
