<?php

namespace PictaStudio\Contento\Models\Scopes;

use Illuminate\Database\Eloquent\{Builder, Model, Scope};
use PictaStudio\Contento\Models\Scopes\Concerns\CanBeExcludedByRequest;

class Published implements Scope
{
    use CanBeExcludedByRequest;

    public function __construct(
        private string $column = 'published_at',
        private ?bool $allowNull = null
    ) {
        $this->allowNull ??= config('contento.scopes.published.allow_null', false);
    }

    public function apply(Builder $builder, Model $model): void
    {
        if ($this->shouldExcludeScope('exclude_published_scope')) {
            return;
        }

        if (request()->routeIs(config('contento.scopes.routes_to_exclude', []))) {
            return;
        }

        $qualifiedColumn = $model->qualifyColumn($this->column);

        $builder->where(function (Builder $query) use ($qualifiedColumn): void {
            $query->where($qualifiedColumn, '<=', now());

            if ($this->allowNull) {
                $query->orWhereNull($qualifiedColumn);
            }
        });
    }
}
