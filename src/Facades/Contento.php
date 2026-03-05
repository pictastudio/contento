<?php

namespace PictaStudio\Contento\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;

/**
 * @see PictaStudio\Contento\Contento
 *
 * @method static void configureUsing(Closure $callback)
 */
class Contento extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'contento';
    }
}
