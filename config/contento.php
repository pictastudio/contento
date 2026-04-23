<?php

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
        'metadata' => Models\Metadata::class,
        'setting' => Models\Setting::class,
        'user' => env('CONTENTO_USER_MODEL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authors
    |--------------------------------------------------------------------------
    |
    | Author tracking is opt-in so the package does not assume a host auth
    | model exists. When enabled, created_by and updated_by are populated with
    | the authenticated user's identifier.
    |
    */
    'authors' => [
        'track' => env('CONTENTO_TRACK_AUTHORS', false),
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
        Contracts\MetadataValidationRules::class => Validations\MetadataValidation::class,
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
        'metadata' => 'metadata',
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
            'json_resource_enable_wrapping' => env('CONTENTO_ROUTES_API_JSON_RESOURCE_ENABLE_WRAPPING', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | Optional default settings records used by the publishable migration.
    |
    */
    'settings' => [
        'default_records' => [
            [
                'group' => 'general',
                'name' => 'website_name',
                'value' => env('APP_NAME'),
            ],
            [
                'group' => 'general',
                'name' => 'website_footer',
                'value' => null,
            ],
            [
                'group' => 'general',
                'name' => 'bottom_text',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'email',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'address',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'city',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'zip',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'province',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'country',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'vat',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'pec',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'fiscal_code',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'sdi',
                'value' => null,
            ],
            [
                'group' => 'company',
                'name' => 'iban',
                'value' => null,
            ],
            [
                'group' => 'social',
                'name' => 'facebook',
                'value' => null,
            ],
            [
                'group' => 'social',
                'name' => 'instagram',
                'value' => null,
            ],
            [
                'group' => 'social',
                'name' => 'linkedin',
                'value' => null,
            ],
            [
                'group' => 'social',
                'name' => 'x',
                'value' => null,
            ],
            [
                'group' => 'social',
                'name' => 'youtube',
                'value' => null,
            ],
            [
                'group' => 'social',
                'name' => 'tiktok',
                'value' => null,
            ],
            [
                'group' => 'analytics',
                'name' => 'facebook_pixel_id',
                'value' => null,
            ],
            [
                'group' => 'analytics',
                'name' => 'google_analytics_key',
                'value' => null,
            ],
            [
                'group' => 'analytics',
                'name' => 'google_analytics_snippet',
                'value' => null,
            ],
            [
                'group' => 'metadata',
                'name' => 'title',
                'value' => null,
            ],
            [
                'group' => 'metadata',
                'name' => 'author',
                'value' => null,
            ],
            [
                'group' => 'metadata',
                'name' => 'description',
                'value' => null,
            ],
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
