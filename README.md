# cms library to manage dynamic content

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pictastudio/contento.svg?style=flat-square)](https://packagist.org/packages/pictastudio/contento)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/pictastudio/contento/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/pictastudio/contento/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/pictastudio/contento/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/pictastudio/contento/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/pictastudio/contento.svg?style=flat-square)](https://packagist.org/packages/pictastudio/contento)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/contento.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/contento)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require pictastudio/contento
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="contento-migrations"
php artisan migrate
```

If you are upgrading from an earlier release, publish the latest package migrations again before running `php artisan migrate`. Recent upgrades add the `menu_items.path`, `menu_items.sort_order`, and `faqs.sort_order` columns through package migrations.

You can publish the config file with:

```bash
php artisan vendor:publish --tag="contento-config"
```

This is the contents of the published config file:

```php
return [
    'authorize_using_policies' => env('CONTENTO_AUTHORIZE_USING_POLICIES', true),
    'models' => [
        'page' => \PictaStudio\Contento\Models\Page::class,
        'faq_category' => \PictaStudio\Contento\Models\FaqCategory::class,
        'faq' => \PictaStudio\Contento\Models\Faq::class,
        'mail_form' => \PictaStudio\Contento\Models\MailForm::class,
        'modal' => \PictaStudio\Contento\Models\Modal::class,
        'content_tag' => \PictaStudio\Contento\Models\ContentTag::class,
        'setting' => \PictaStudio\Contento\Models\Setting::class,
    ],
    'table_names' => [
        'pages' => 'pages',
        'faq_categories' => 'faq_categories',
        'faqs' => 'faqs',
        'mail_forms' => 'mail_forms',
        'modals' => 'modals',
        'content_tags' => 'content_tags',
        'content_taggables' => 'content_taggables',
        'settings' => 'settings',
    ],
    'routes' => [
        'api' => [
            'v1' => [
                'prefix' => 'api/contento/v1',
                'name' => 'api.contento.v1',
                'middleware' => ['api'],
                'pagination' => [
                    'per_page' => 15,
                    'max_per_page' => 100,
                ],
            ],
            'enable' => true,
            'json_resource_enable_wrapping' => true,
        ],
    ],
];
```

## Usage

This package provides a headless CMS API. Once installed and migrated, you can access the following endpoints:

- `GET /api/contento/v1/pages` - List all pages
- `GET /api/contento/v1/pages/{id_or_slug}` - Get a single page
- `GET /api/contento/v1/menus` - List menus
- `GET /api/contento/v1/menus/{id_or_slug}` - Get a single menu
- `GET /api/contento/v1/menu-items` - List menu items
- `GET /api/contento/v1/menu-items/{id_or_slug}` - Get a single menu item
- `POST /api/contento/v1/menu-items/bulk/upsert` - Create and update menu items in a single request
- `GET /api/contento/v1/faq-categories` - List FAQ categories with questions
- `POST /api/contento/v1/faqs/bulk/upsert` - Create and update FAQs in a single request
- `GET /api/contento/v1/settings` - List all settings (always non-paginated)
- `POST /api/contento/v1/settings/bulk/update` - Create and update settings in a single request

All endpoints return JSON responses using Laravel API Resources.

Pages accept a nullable `metadata` object for structured page metadata such as SEO fields.

### Common list query parameters

Most index endpoints support:

- `page`
- `per_page`
- `sort_by`
- `sort_dir`
- `exclude_all_scopes`
- `exclude_active_scope`
- `exclude_date_range_scope`

Pages also support `exclude_published_scope`.

Menus, modals, and content tags also support `all=1` or `filter=all` to return every matching record as a non-paginated collection while skipping their implicit active and visibility date-range scopes.

### Menu query parameters

`GET /api/contento/v1/menus` supports:

- `id[]`
- `title`
- `slug`
- `active` or `is_active`
- `visible_date_from`, `visible_date_from_start`, `visible_date_from_end`
- `visible_date_to`, `visible_date_to_start`, `visible_date_to_end`
- `created_at_start`, `created_at_end`
- `updated_at_start`, `updated_at_end`
- `include=items`

### Menu item query parameters

`GET /api/contento/v1/menu-items` supports:

- `id[]`
- `menu_id`
- `parent_id`
- `title`
- `slug`
- `link`
- `active` or `is_active`
- `visible_date_from`, `visible_date_from_start`, `visible_date_from_end`
- `visible_date_to`, `visible_date_to_start`, `visible_date_to_end`
- `created_at_start`, `created_at_end`
- `updated_at_start`, `updated_at_end`
- `as_tree=1`
- `include=menu,parent,children`

`DELETE /api/contento/v1/menu-items/{menuItem}` preserves descendants by default: direct children move to the deleted item's parent and paths are rebuilt recursively. Add `delete_children=1` to recursively delete the target item and every descendant.

`DELETE /api/contento/v1/content-tags/{contentTag}` uses the same tree delete behavior. Recursive content tag deletes also clear tag associations for every deleted tag.

### Authorization

Policy authorization is optional and follows host app policy registration.

Register policies in your app and keep `contento.authorize_using_policies` enabled:

```php
use App\Models\Page;
use App\Policies\PagePolicy;
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::policy(Page::class, PagePolicy::class);
}
```

Controllers check authorization only when:
- `contento.authorize_using_policies` is `true`
- there is an authenticated user
- a matching gate/policy definition exists

## Testing

The package uses [Pest](https://pestphp.com/) for testing. You can run the tests using:

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Frameck](https://github.com/Frameck)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
