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
