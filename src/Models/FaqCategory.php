<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PictaStudio\Contento\Traits\HasAuthors;

class FaqCategory extends Model
{
    use HasFactory, HasAuthors;

    protected $guarded = ['id'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function getTable()
    {
        return config('contento.table_names.faq_categories', parent::getTable());
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }
}
