<?php

namespace PictaStudio\Contento\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \PictaStudio\Contento\Contento
 */
class Contento extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \PictaStudio\Contento\Contento::class;
    }
}
