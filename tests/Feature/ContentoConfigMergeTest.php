<?php

namespace PictaStudio\Contento\Tests\Feature;

use Orchestra\Testbench\Attributes\WithConfig;
use PictaStudio\Contento\Tests\TestCase;

final class ContentoConfigMergeTest extends TestCase
{
    #[WithConfig('contento', [
        'routes' => [
            'api' => [
                'v1' => [
                    'prefix' => 'custom/contento',
                ],
            ],
        ],
    ])]
    public function test_published_host_config_inherits_missing_nested_defaults(): void
    {
        $this->assertSame('custom/contento', config('contento.routes.api.v1.prefix'));
        $this->assertSame('api.contento.v1', config('contento.routes.api.v1.name'));
        $this->assertSame(15, config('contento.routes.api.v1.pagination.per_page'));
        $this->assertSame(100, config('contento.routes.api.v1.pagination.max_per_page'));
        $this->assertTrue(config('contento.routes.api.enable'));
        $this->assertFalse(config('contento.routes.api.json_resource_enable_wrapping'));
    }

    #[WithConfig('contento', [
        'catalog_images' => [
            'allowed_mimetypes' => [
                'image/avif',
            ],
        ],
    ])]
    public function test_published_host_list_config_remains_a_full_override(): void
    {
        $this->assertSame(
            ['image/avif'],
            config('contento.catalog_images.allowed_mimetypes')
        );

        $this->assertSame('public', config('contento.catalog_images.disk'));
        $this->assertSame('catalog_images', config('contento.catalog_images.directory'));
    }
}
