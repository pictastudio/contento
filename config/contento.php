<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the prefix and middleware for the package routes.
    |
    */
    'prefix' => 'api/contento/v1',

    'middleware' => ['api'],

    'authorize_using_policies' => env('CONTENTO_AUTHORIZE_USING_POLICIES', true),

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
        'faq_categories' => 'faq_categories',
        'faqs' => 'faqs',
        'mail_forms' => 'mail_forms',
        'modals' => 'modals',
        'settings' => 'settings',
    ],
];
