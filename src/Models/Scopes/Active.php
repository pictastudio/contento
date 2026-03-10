<?php

namespace PictaStudio\Contento\Models\Scopes;

use Illuminate\Database\Eloquent\{Builder, Model, Scope};
use PictaStudio\Contento\Models\Scopes\Concerns\CanBeExcludedByRequest;

class Active implements Scope
{
    use CanBeExcludedByRequest;

    public function apply(Builder $builder, Model $model): void
    {
        if ($this->shouldExcludeScope('exclude_active_scope')) {
            return;
        }

        if (request()->routeIs(config('contento.scopes.routes_to_exclude', []))) {
            return;
        }

        $builder->where($model->qualifyColumn('active'), true);
    }
}
