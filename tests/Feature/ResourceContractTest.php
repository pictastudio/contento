<?php

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Route;
use PictaStudio\Contento\Models\{CatalogImage, ContentTag, Faq, FaqCategory, MailForm, Menu, MenuItem, Metadata, Modal, Page};

use function Pest\Laravel\{getJson, postJson};

it('returns stable public fields for each resource', function (callable $resolveResponse, array $expectedKeys) {
    $response = $resolveResponse();

    expect(array_keys(contentoResourceJson($response)))->toBe($expectedKeys);
})->with([
    'page' => [
        fn () => getJson(config('contento.routes.api.v1.prefix') . '/pages/' . Page::factory()->create()->getKey())->assertOk(),
        ['id', 'title', 'slug', 'type', 'active', 'important', 'visible_date_from', 'visible_date_to', 'published_at', 'author', 'abstract', 'content', 'metadata', 'created_by', 'updated_by', 'created_at', 'updated_at'],
    ],
    'menu' => [
        fn () => getJson(config('contento.routes.api.v1.prefix') . '/menus/' . Menu::factory()->create()->getKey())->assertOk(),
        ['id', 'title', 'slug', 'active', 'visible_date_from', 'visible_date_to', 'created_by', 'updated_by', 'created_at', 'updated_at'],
    ],
    'menu item' => [
        fn () => getJson(config('contento.routes.api.v1.prefix') . '/menu-items/' . MenuItem::factory()->create()->getKey())->assertOk(),
        ['id', 'menu_id', 'parent_id', 'path', 'title', 'slug', 'link', 'active', 'sort_order', 'visible_date_from', 'visible_date_to', 'created_by', 'updated_by', 'created_at', 'updated_at'],
    ],
    'faq category' => [
        fn () => getJson(config('contento.routes.api.v1.prefix') . '/faq-categories/' . FaqCategory::factory()->create()->getKey())->assertOk(),
        ['id', 'title', 'slug', 'abstract', 'active', 'created_by', 'updated_by', 'created_at', 'updated_at', 'faqs'],
    ],
    'faq' => [
        fn () => getJson(config('contento.routes.api.v1.prefix') . '/faqs/' . Faq::factory()->create()->getKey())->assertOk(),
        ['id', 'faq_category_id', 'title', 'slug', 'content', 'active', 'sort_order', 'visible_date_from', 'visible_date_to', 'created_by', 'updated_by', 'created_at', 'updated_at'],
    ],
    'mail form' => [
        fn () => getJson(config('contento.routes.api.v1.prefix') . '/mail-forms/' . MailForm::factory()->create()->getKey())->assertOk(),
        ['id', 'name', 'slug', 'email_to', 'email_cc', 'email_bcc', 'custom_fields', 'redirect_url', 'custom_data', 'options', 'newsletter', 'created_by', 'updated_by', 'created_at', 'updated_at'],
    ],
    'modal' => [
        fn () => getJson(config('contento.routes.api.v1.prefix') . '/modals/' . Modal::factory()->create()->getKey())->assertOk(),
        ['id', 'title', 'slug', 'content', 'cta_button_text', 'cta_button_url', 'cta_button_color', 'image', 'template', 'timeout', 'popup_time', 'show_on_all_pages', 'active', 'visible_date_from', 'visible_date_to', 'created_by', 'updated_by', 'created_at', 'updated_at'],
    ],
    'content tag' => [
        fn () => getJson(config('contento.routes.api.v1.prefix') . '/content-tags/' . ContentTag::factory()->create()->getKey())->assertOk(),
        ['id', 'parent_id', 'path', 'name', 'slug', 'abstract', 'description', 'metadata', 'images', 'active', 'show_in_menu', 'in_evidence', 'sort_order', 'visible_from', 'visible_until', 'created_by', 'updated_by', 'created_at', 'updated_at'],
    ],
    'catalog image' => [
        fn () => getJson(config('contento.routes.api.v1.prefix') . '/catalog-images/' . CatalogImage::factory()->create()->getKey())->assertOk(),
        ['id', 'name', 'title', 'alt', 'caption', 'disk', 'path', 'url', 'mime_type', 'size', 'width', 'height', 'metadata', 'created_by', 'updated_by', 'created_at', 'updated_at'],
    ],
    'metadata' => [
        fn () => getJson(config('contento.routes.api.v1.prefix') . '/metadata/' . Metadata::factory()->create()->getKey())->assertOk(),
        ['id', 'name', 'slug', 'uri', 'metadata', 'created_at', 'updated_at'],
    ],
    'setting' => [
        fn () => postJson(config('contento.routes.api.v1.prefix') . '/settings', [
            'group' => 'site',
            'name' => 'title',
            'value' => 'My site',
        ])->assertCreated(),
        ['id', 'group', 'name', 'value', 'created_at', 'updated_at'],
    ],
]);

it('applies wrapping configuration only to contento resources', function () {
    config()->set('contento.routes.api.json_resource_enable_wrapping', false);

    Route::get('/resource-wrap-check', function () {
        return new class(['message' => 'ok']) extends JsonResource
        {
            public function toArray(Request $request): array
            {
                return $this->resource;
            }
        };
    });

    $page = Page::factory()->create();

    getJson(config('contento.routes.api.v1.prefix') . '/pages/' . $page->getKey())
        ->assertOk()
        ->assertJsonPath('id', $page->getKey())
        ->assertJsonMissingPath('data.id');

    getJson('/resource-wrap-check')
        ->assertOk()
        ->assertJsonPath('data.message', 'ok');
});
