<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

abstract class BaseController extends Controller
{
    use AuthorizesRequests;

    protected function authorizeIfConfigured(string $ability, mixed $arguments): void
    {
        if (!config('contento.authorize_using_policies')) {
            return;
        }

        if (!auth()->check()) {
            return;
        }

        if (!$this->hasAuthorizationDefinition($ability, $arguments)) {
            return;
        }

        Gate::forUser(auth()->user())->inspect($ability, $arguments)->authorize();
    }

    protected function hasAuthorizationDefinition(string $ability, mixed $arguments): bool
    {
        if (Gate::has($ability)) {
            return true;
        }

        if (is_string($arguments) && class_exists($arguments)) {
            return Gate::getPolicyFor($arguments) !== null;
        }

        if (is_object($arguments)) {
            return Gate::getPolicyFor($arguments) !== null;
        }

        return false;
    }

    protected function applyArrayFilters(Builder $query, array $validated, array $filters): void
    {
        foreach ($filters as $parameter => $column) {
            if (!array_key_exists($parameter, $validated) || !is_array($validated[$parameter])) {
                continue;
            }

            $query->whereIn($column, $validated[$parameter]);
        }
    }

    protected function applyExactFilters(Builder $query, array $validated, array $filters): void
    {
        foreach ($filters as $parameter => $column) {
            if (!array_key_exists($parameter, $validated)) {
                continue;
            }

            $query->where($column, $validated[$parameter]);
        }
    }

    protected function applyDateRangeFilters(Builder $query, array $validated, array $ranges): void
    {
        foreach ($ranges as $column => $parameters) {
            $startParameter = $parameters['start'] ?? null;
            $endParameter = $parameters['end'] ?? null;

            if (is_string($startParameter) && array_key_exists($startParameter, $validated)) {
                $query->where($column, '>=', $validated[$startParameter]);
            }

            if (is_string($endParameter) && array_key_exists($endParameter, $validated)) {
                $query->where($column, '<=', $validated[$endParameter]);
            }
        }
    }

    protected function applyNumericRangeFilters(Builder $query, array $validated, array $ranges): void
    {
        foreach ($ranges as $column => $parameters) {
            $minParameter = $parameters['min'] ?? null;
            $maxParameter = $parameters['max'] ?? null;

            if (is_string($minParameter) && array_key_exists($minParameter, $validated)) {
                $query->where($column, '>=', $validated[$minParameter]);
            }

            if (is_string($maxParameter) && array_key_exists($maxParameter, $validated)) {
                $query->where($column, '<=', $validated[$maxParameter]);
            }
        }
    }

    protected function applySorting(Builder $query, array $validated, string $defaultSortBy = 'id', string $defaultSortDir = 'desc'): void
    {
        $query->orderBy(
            (string) ($validated['sort_by'] ?? $defaultSortBy),
            (string) ($validated['sort_dir'] ?? $defaultSortDir)
        );
    }

    protected function resolvePerPage(array $validated): int
    {
        $defaultPerPage = max(
            1,
            (int) config('contento.routes.api.v1.pagination.per_page', 15)
        );
        $maxPerPage = max(
            1,
            (int) config('contento.routes.api.v1.pagination.max_per_page', 100)
        );
        $perPage = (int) ($validated['per_page'] ?? $defaultPerPage);

        if ($perPage < 1) {
            return 1;
        }

        return min($perPage, $maxPerPage);
    }
}
