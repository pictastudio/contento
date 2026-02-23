<?php

namespace PictaStudio\Contento\Http\Controllers;

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
}
