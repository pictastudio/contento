<?php

namespace PictaStudio\Contento\Traits;

use Illuminate\Database\Eloquent\Model;

trait HasSlugRouteBinding
{
    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->where('id', $value)
            ->orWhere('slug', $value)
            ->first();
    }
}
