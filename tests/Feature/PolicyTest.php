<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use PictaStudio\Contento\Http\Controllers\BaseController;
use PictaStudio\Contento\Models\{Page, Setting};

use function Pest\Laravel\actingAs;

it('checks authorization using the registered policy when authorize_using_policies is true', function () {
    config(['contento.authorize_using_policies' => true]);

    Gate::policy(Page::class, TestPagePolicyDenyView::class);

    $user = new GenericUser(['id' => 1]);
    $page = Page::factory()->create();

    actingAs($user);

    $controller = controllerThatAuthorizes();

    $controller->testAuthorize('view', $page);
})->throws(AuthorizationException::class)->group('policy');

it('does not throw when policy allows and authorize_using_policies is true', function () {
    config(['contento.authorize_using_policies' => true]);

    Gate::policy(Page::class, TestPagePolicyAllowAll::class);

    $user = new GenericUser(['id' => 1]);
    $page = Page::factory()->create();

    actingAs($user);

    $controller = controllerThatAuthorizes();

    $controller->testAuthorize('view', $page);

    expect(true)->toBeTrue();
})->group('policy');

it('does not check authorization when authorize_using_policies is false', function () {
    config(['contento.authorize_using_policies' => false]);

    Gate::policy(Page::class, TestPagePolicyDenyView::class);

    $user = new GenericUser(['id' => 1]);
    $page = Page::factory()->create();

    actingAs($user);

    $controller = controllerThatAuthorizes();

    $controller->testAuthorize('view', $page);

    expect(true)->toBeTrue();
})->group('policy');

it('does not check authorization when no policy or gate definition is registered', function () {
    config(['contento.authorize_using_policies' => true]);

    $user = new GenericUser(['id' => 1]);

    actingAs($user);

    $controller = controllerThatAuthorizes();

    $controller->testAuthorize('viewAny', Setting::class);

    expect(true)->toBeTrue();
})->group('policy');

function controllerThatAuthorizes(): object
{
    return new class extends BaseController
    {
        public function testAuthorize(string $ability, mixed $arguments): void
        {
            $this->authorizeIfConfigured($ability, $arguments);
        }
    };
}

class TestPagePolicyDenyView
{
    public function view(Authenticatable $user, Page $page): bool
    {
        return false;
    }
}

class TestPagePolicyAllowAll
{
    public function view(Authenticatable $user, Page $page): bool
    {
        return true;
    }
}
