<?php

namespace PictaStudio\Contento\Actions\Galleries;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use function PictaStudio\Contento\Helpers\Functions\query;

class CreateGallery
{
    public function handle(array $payload): Model
    {
        return DB::transaction(fn (): Model => query('gallery')->create($payload));
    }
}
