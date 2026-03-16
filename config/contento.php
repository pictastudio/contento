<?php

use Illuminate\Foundation\Auth\User;
use PictaStudio\Contento\{Models, Validations};
use PictaStudio\Contento\Validations\Contracts;

return [
    'authorize_using_policies' => env('CONTENTO_AUTHORIZE_USING_POLICIES', true),

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Host applications can override any model class to extend behavior.
    |
    */
    'models' => [
        'page' => Models\Page::class,
        'menu' => Models\Menu::class,
        'menu_item' => Models\MenuItem::class,
        'faq_category' => Models\FaqCategory::class,
        'faq' => Models\Faq::class,
        'mail_form' => Models\MailForm::class,
        'modal' => Models\Modal::class,
        'content_tag' => Models\ContentTag::class,
        'setting' => Models\Setting::class,
        'user' => env('CONTENTO_USER_MODEL', User::class),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    |
    | Map validation contract (interface) to implementation. The service provider
    | binds these into the container so Form Requests resolve the correct rules.
    |
    */
    'validations' => [
        Contracts\ContentTagValidationRules::class => Validations\ContentTagValidation::class,
        Contracts\FaqCategoryValidationRules::class => Validations\FaqCategoryValidation::class,
        Contracts\FaqValidationRules::class => Validations\FaqValidation::class,
        Contracts\MailFormValidationRules::class => Validations\MailFormValidation::class,
        Contracts\MenuItemValidationRules::class => Validations\MenuItemValidation::class,
        Contracts\MenuValidationRules::class => Validations\MenuValidation::class,
        Contracts\ModalValidationRules::class => Validations\ModalValidation::class,
        Contracts\PageValidationRules::class => Validations\PageValidation::class,
        Contracts\SettingValidationRules::class => Validations\SettingValidation::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the table names for the package entities.
    |
    */
    'table_names' => [
        'pages' => 'pages',
        'menus' => 'menus',
        'menu_items' => 'menu_items',
        'faq_categories' => 'faq_categories',
        'faqs' => 'faqs',
        'mail_forms' => 'mail_forms',
        'modals' => 'modals',
        'content_tags' => 'content_tags',
        'content_taggables' => 'content_taggables',
        'settings' => 'settings',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    */
    'routes' => [
        'api' => [
            'v1' => [
                'prefix' => env('CONTENTO_API_V1_PREFIX', 'api/contento/v1'),
                'name' => env('CONTENTO_API_V1_NAME', 'api.contento.v1'),
                'middleware' => [
                    'api',
                    // 'auth:sanctum',
                ],
                'pagination' => [
                    'per_page' => 15,
                    'max_per_page' => 100,
                ],
            ],
            'enable' => env('CONTENTO_ROUTES_API_ENABLE', true),
            'json_resource_enable_wrapping' => env('CONTENTO_ROUTES_API_JSON_RESOURCE_ENABLE_WRAPPING', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    |
    | Public content queries can opt out of these global scopes by request or
    | by route pattern. This keeps package defaults safe while remaining
    | host-application friendly.
    |
    */
    'scopes' => [
        'routes_to_exclude' => [],
        'in_date_range' => [
            'include_start_date' => true,
            'include_end_date' => true,
            'allow_null' => true,
        ],
        'published' => [
            'allow_null' => true,
        ],
    ],
];
