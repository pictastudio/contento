<?php

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Gate, Storage};
use PictaStudio\Contento\Models\CatalogImage;

use function Pest\Laravel\{actingAs, assertDatabaseHas, deleteJson, getJson, post};

it('can list catalog images with filters sorting and pagination', function () {
    $first = CatalogImage::factory()->create([
        'name' => 'Hero image',
        'title' => 'Landing hero',
        'alt' => 'Hero alt text',
        'mime_type' => 'image/jpeg',
        'size' => 1200,
    ]);
    CatalogImage::factory()->create([
        'name' => 'Small icon',
        'title' => 'Icon title',
        'alt' => 'Icon alt text',
        'mime_type' => 'image/png',
        'size' => 300,
    ]);
    $third = CatalogImage::factory()->create([
        'name' => 'Hero detail',
        'title' => 'Detail hero',
        'alt' => 'Detail alt text',
        'mime_type' => 'image/jpeg',
        'size' => 2400,
    ]);

    $query = http_build_query([
        'id' => [$first->getKey(), $third->getKey()],
        'name' => 'hero',
        'mime_type' => 'jpeg',
        'size_min' => 1000,
        'sort_by' => 'size',
        'sort_dir' => 'desc',
        'per_page' => 1,
    ]);

    getJson(config('contento.routes.api.v1.prefix') . '/catalog-images?' . $query)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $third->getKey())
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 2);
});

it('can list all catalog images with the all filter', function () {
    CatalogImage::factory()->count(3)->create();

    getJson(config('contento.routes.api.v1.prefix') . '/catalog-images?all=1&per_page=1')
        ->assertOk()
        ->assertJsonCount(3, contentoCollectionPath())
        ->assertJsonMissingPath('meta')
        ->assertJsonMissingPath('links');
});

it('rejects unsupported catalog image list query params', function () {
    getJson(config('contento.routes.api.v1.prefix') . '/catalog-images?unknown=1')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['unknown']);
});

it('can upload a catalog image with seo metadata', function () {
    Storage::fake('public');

    $response = post(
        config('contento.routes.api.v1.prefix') . '/catalog-images',
        [
            'file' => UploadedFile::fake()->image('hero.jpg', 640, 480),
            'name' => 'Hero',
            'title' => 'SEO hero title',
            'alt' => 'SEO hero alt',
            'caption' => 'Hero caption',
            'metadata' => ['placement' => 'home'],
        ],
        ['Accept' => 'application/json']
    )->assertCreated()
        ->assertJsonPath(contentoResourcePath('name'), 'Hero')
        ->assertJsonPath(contentoResourcePath('title'), 'SEO hero title')
        ->assertJsonPath(contentoResourcePath('alt'), 'SEO hero alt')
        ->assertJsonPath(contentoResourcePath('caption'), 'Hero caption')
        ->assertJsonPath(contentoResourcePath('disk'), 'public')
        ->assertJsonPath(contentoResourcePath('mime_type'), 'image/jpeg')
        ->assertJsonPath(contentoResourcePath('width'), 640)
        ->assertJsonPath(contentoResourcePath('height'), 480);

    $catalogImage = CatalogImage::query()->findOrFail($response->json(contentoResourcePath('id')));

    assertDatabaseHas(config('contento.table_names.catalog_images'), [
        'id' => $catalogImage->getKey(),
        'title' => 'SEO hero title',
        'alt' => 'SEO hero alt',
        'disk' => 'public',
    ]);

    Storage::disk('public')->assertExists($catalogImage->path);
});

it('can update catalog image seo metadata without a new upload', function () {
    $catalogImage = CatalogImage::factory()->create([
        'title' => 'Old title',
        'alt' => 'Old alt',
    ]);

    post(
        config('contento.routes.api.v1.prefix') . '/catalog-images/' . $catalogImage->getKey(),
        [
            '_method' => 'PATCH',
            'title' => 'New SEO title',
            'alt' => 'New SEO alt',
            'caption' => 'New caption',
        ],
        ['Accept' => 'application/json']
    )->assertOk()
        ->assertJsonPath(contentoResourcePath('title'), 'New SEO title')
        ->assertJsonPath(contentoResourcePath('alt'), 'New SEO alt')
        ->assertJsonPath(contentoResourcePath('path'), $catalogImage->path);
});

it('can replace the uploaded file while preserving editable metadata', function () {
    Storage::fake('public');

    $oldPath = 'catalog_images/old.jpg';
    Storage::disk('public')->put($oldPath, 'old');

    $catalogImage = CatalogImage::factory()->create([
        'disk' => 'public',
        'path' => $oldPath,
        'title' => 'Existing title',
        'alt' => 'Existing alt',
    ]);

    post(
        config('contento.routes.api.v1.prefix') . '/catalog-images/' . $catalogImage->getKey(),
        [
            '_method' => 'PATCH',
            'file' => UploadedFile::fake()->image('replacement.png', 320, 240),
        ],
        ['Accept' => 'application/json']
    )->assertOk()
        ->assertJsonPath(contentoResourcePath('title'), 'Existing title')
        ->assertJsonPath(contentoResourcePath('alt'), 'Existing alt')
        ->assertJsonPath(contentoResourcePath('mime_type'), 'image/png')
        ->assertJsonPath(contentoResourcePath('width'), 320)
        ->assertJsonPath(contentoResourcePath('height'), 240);

    $catalogImage->refresh();

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($catalogImage->path);
});

it('can delete a catalog image and its file by default', function () {
    Storage::fake('public');

    $path = 'catalog_images/delete-me.jpg';
    Storage::disk('public')->put($path, 'content');

    $catalogImage = CatalogImage::factory()->create([
        'disk' => 'public',
        'path' => $path,
    ]);

    deleteJson(config('contento.routes.api.v1.prefix') . '/catalog-images/' . $catalogImage->getKey())
        ->assertNoContent();

    expect(CatalogImage::query()->whereKey($catalogImage->getKey())->exists())->toBeFalse();
    Storage::disk('public')->assertMissing($path);
});

it('can keep catalog image files on delete when configured', function () {
    Storage::fake('public');
    config()->set('contento.catalog_images.delete_file_on_destroy', false);

    $path = 'catalog_images/keep-me.jpg';
    Storage::disk('public')->put($path, 'content');

    $catalogImage = CatalogImage::factory()->create([
        'disk' => 'public',
        'path' => $path,
    ]);

    deleteJson(config('contento.routes.api.v1.prefix') . '/catalog-images/' . $catalogImage->getKey())
        ->assertNoContent();

    Storage::disk('public')->assertExists($path);
});

it('validates catalog image uploads', function () {
    Storage::fake('public');

    post(
        config('contento.routes.api.v1.prefix') . '/catalog-images',
        [],
        ['Accept' => 'application/json']
    )->assertUnprocessable()
        ->assertJsonValidationErrors(['file']);

    post(
        config('contento.routes.api.v1.prefix') . '/catalog-images',
        ['file' => UploadedFile::fake()->create('document.pdf', 10, 'application/pdf')],
        ['Accept' => 'application/json']
    )->assertUnprocessable()
        ->assertJsonValidationErrors(['file']);
});

it('honors custom catalog image storage config', function () {
    Storage::fake('local');
    config()->set('contento.catalog_images.disk', 'local');
    config()->set('contento.catalog_images.directory', 'custom/catalog');

    $response = post(
        config('contento.routes.api.v1.prefix') . '/catalog-images',
        ['file' => UploadedFile::fake()->image('custom.jpg')],
        ['Accept' => 'application/json']
    )->assertCreated()
        ->assertJsonPath(contentoResourcePath('disk'), 'local');

    $catalogImage = CatalogImage::query()->findOrFail($response->json(contentoResourcePath('id')));

    expect(str_starts_with($catalogImage->path, 'custom/catalog/'))->toBeTrue();
    Storage::disk('local')->assertExists($catalogImage->path);
});

it('uses catalog image policy hooks when configured', function () {
    Storage::fake('public');
    config(['contento.authorize_using_policies' => true]);
    Gate::policy(CatalogImage::class, TestCatalogImagePolicyCreateDenied::class);

    actingAs(new GenericUser(['id' => 1]));

    post(
        config('contento.routes.api.v1.prefix') . '/catalog-images',
        ['file' => UploadedFile::fake()->image('denied.jpg')],
        ['Accept' => 'application/json']
    )->assertForbidden();
});

it('uses configured catalog image models for member routes', function () {
    config(['contento.models.catalog_image' => TestConfiguredCatalogImage::class]);
    config(['contento.authorize_using_policies' => true]);
    Gate::policy(TestConfiguredCatalogImage::class, TestConfiguredCatalogImagePolicyDenyView::class);

    $catalogImage = CatalogImage::factory()->create();

    actingAs(new GenericUser(['id' => 1]));

    getJson(config('contento.routes.api.v1.prefix') . '/catalog-images/' . $catalogImage->getKey())
        ->assertForbidden();
});

class TestCatalogImagePolicyCreateDenied
{
    public function create(Authenticatable $user): bool
    {
        return false;
    }
}

class TestConfiguredCatalogImage extends CatalogImage {}

class TestConfiguredCatalogImagePolicyDenyView
{
    public function view(Authenticatable $user, TestConfiguredCatalogImage $catalogImage): bool
    {
        return false;
    }
}
