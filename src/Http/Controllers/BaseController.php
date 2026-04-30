<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use PictaStudio\Contento\Models\Scopes\{Active, Published};

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

    protected function requestsAllRecords(array $validated): bool
    {
        return ($validated['filter'] ?? null) === 'all'
            || (
                array_key_exists('all', $validated)
                && filter_var($validated['all'], FILTER_VALIDATE_BOOLEAN)
            );
    }

    protected function removeImplicitScopesForAllFilter(
        Builder $query,
        array $validated,
        bool $supportsActiveScope = false,
        array $dateRangeScopes = []
    ): Builder {
        if (!$this->requestsAllRecords($validated)) {
            return $query;
        }

        if ($supportsActiveScope) {
            $query->withoutGlobalScope(Active::class);
        }

        foreach ($dateRangeScopes as $scope) {
            $query->withoutGlobalScope($scope);
        }

        return $query;
    }

    protected function applyTextFilters(Builder $query, array $validated, array $filters): void
    {
        foreach ($filters as $parameter => $column) {
            if (!array_key_exists($parameter, $validated) || !is_string($validated[$parameter])) {
                continue;
            }

            $wrappedColumn = $query->getQuery()->getGrammar()->wrap($column);

            $query->whereRaw(
                sprintf("LOWER(%s) LIKE ? ESCAPE '!'", $wrappedColumn),
                [$this->caseInsensitiveContainsPattern($validated[$parameter])]
            );
        }
    }

    protected function removeImplicitScopesOverriddenByExplicitFilters(
        Builder $query,
        array $validated,
        bool $supportsActiveScope = false,
        array $dateRangeColumns = [],
        bool $supportsPublishedScope = false,
        string $publishedColumn = 'published_at'
    ): Builder {
        if ($supportsActiveScope && array_key_exists('active', $validated)) {
            $query->withoutGlobalScope(Active::class);
        }

        foreach ($dateRangeColumns as $column => $scope) {
            if (
                array_key_exists($column, $validated)
                || array_key_exists($column . '_start', $validated)
                || array_key_exists($column . '_end', $validated)
            ) {
                $query->withoutGlobalScope($scope);
            }
        }

        if (
            $supportsPublishedScope
            && (
                array_key_exists($publishedColumn, $validated)
                || array_key_exists($publishedColumn . '_start', $validated)
                || array_key_exists($publishedColumn . '_end', $validated)
            )
        ) {
            $query->withoutGlobalScope(Published::class);
        }

        return $query;
    }

    protected function applyDateRangeFilters(Builder $query, array $validated, array $ranges): void
    {
        foreach ($ranges as $column => $parameters) {
            if (array_key_exists($column, $validated)) {
                $query->where($column, $validated[$column]);
            }

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

    protected function caseInsensitiveContainsPattern(string $value): string
    {
        return '%' . mb_strtolower($this->escapeLikeValue($value)) . '%';
    }

    protected function escapeLikeValue(string $value): string
    {
        return str_replace(
            ['!', '%', '_'],
            ['!!', '!%', '!_'],
            $value
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
