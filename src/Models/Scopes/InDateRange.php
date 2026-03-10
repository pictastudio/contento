<?php

namespace PictaStudio\Contento\Models\Scopes;

use Illuminate\Database\Eloquent\{Builder, Model, Scope};
use PictaStudio\Contento\Models\Scopes\Concerns\CanBeExcludedByRequest;

class InDateRange implements Scope
{
    use CanBeExcludedByRequest;

    public function __construct(
        private ?string $startColumn = null,
        private ?string $endColumn = null,
        private ?bool $includeStartDate = null,
        private ?bool $includeEndDate = null,
        private ?bool $allowNull = null
    ) {
        $this->startColumn ??= 'starts_at';
        $this->endColumn ??= 'ends_at';

        $this->includeStartDate ??= config('contento.scopes.in_date_range.include_start_date', true);
        $this->includeEndDate ??= config('contento.scopes.in_date_range.include_end_date', true);
        $this->allowNull ??= config('contento.scopes.in_date_range.allow_null', true);
    }

    public function apply(Builder $builder, Model $model): void
    {
        if ($this->shouldExcludeScope('exclude_date_range_scope')) {
            return;
        }

        if (request()->routeIs(config('contento.scopes.routes_to_exclude', []))) {
            return;
        }

        $builder->where(function (Builder $query) use ($model): void {
            $this->buildQuery(
                $query,
                $model->qualifyColumn($this->startColumn),
                $this->includeStartDate ? '<=' : '<'
            );
            $this->buildQuery(
                $query,
                $model->qualifyColumn($this->endColumn),
                $this->includeEndDate ? '>=' : '>'
            );
        });
    }

    private function buildQuery(Builder $query, string $column, string $operator): Builder
    {
        return $query->where(function (Builder $subQuery) use ($column, $operator): void {
            $subQuery->where(function (Builder $nestedQuery) use ($column, $operator): void {
                $nestedQuery->when(
                    $this->allowNull,
                    fn (Builder $builder) => $builder->whereNotNull($column)
                )->where($column, $operator, now());
            })->when(
                $this->allowNull,
                fn (Builder $builder) => $builder->orWhereNull($column)
            );
        });
    }
}
