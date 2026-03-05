<?php

namespace PictaStudio\Contento;

use Closure;

class Contento
{
    public static function configureUsing(Closure $callback): void
    {
        $callback(app('contento'));
    }
}
