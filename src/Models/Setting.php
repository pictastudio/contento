<?php

namespace PictaStudio\Contento\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PictaStudio\Contento\Events\{SettingCreated, SettingDeleted, SettingUpdated};

class Setting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $dispatchesEvents = [
        'created' => SettingCreated::class,
        'updated' => SettingUpdated::class,
        'deleted' => SettingDeleted::class,
    ];

    public function getTable(): string
    {
        return (string) config('contento.table_names.settings', parent::getTable());
    }
}
