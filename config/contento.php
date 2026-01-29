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
        'pages' => 'contento_pages',
        'faq_categories' => 'contento_faq_categories',
        'faqs' => 'contento_faqs',
        'mail_forms' => 'contento_mail_forms',
        'modals' => 'contento_modals',
        'settings' => 'contento_settings',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | Here you may specify the user model class used in your application.
    | This is used for the created_by and updated_by relationships.
    |
    */
    'user_model' => 'App\\Models\\User',
];
