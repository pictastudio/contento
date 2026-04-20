<?php

use Illuminate\Testing\TestResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PictaStudio\Contento\Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in(__DIR__);

function contentoResponsePath(?string $suffix = null, bool $alwaysWrap = false): ?string
{
    $prefix = $alwaysWrap || (bool) config('contento.routes.api.json_resource_enable_wrapping', false)
        ? 'data'
        : null;

    $segments = array_values(array_filter([$prefix, $suffix], fn (?string $segment): bool => filled($segment)));

    return $segments === [] ? null : implode('.', $segments);
}

function contentoResourcePath(?string $suffix = null): ?string
{
    return contentoResponsePath($suffix);
}

function contentoCollectionPath(?string $suffix = null): ?string
{
    return contentoResponsePath($suffix);
}

function contentoPaginatedPath(?string $suffix = null): string
{
    return contentoResponsePath($suffix, alwaysWrap: true) ?? 'data';
}

function contentoResourceJson(TestResponse $response): array
{
    /** @var array $payload */
    $payload = $response->json(contentoResourcePath());

    return $payload;
}
