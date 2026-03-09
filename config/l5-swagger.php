<?php

return [
    'api' => [
        'title' => 'شركة المفتاح API',
        'description' => 'API للعقارات ونظام المستخدمين',
        'version' => '1.0.0',
        'contact' => [
            'name' => 'شركة المفتاح',
            'email' => 'info@almiftah.com',
        ],
    ],

    'routes' => [
        'api' => '/api/documentation',
        'docs' => '/docs',
        'oauth2_callback' => '/api/oauth2callback',
    ],

    'paths' => [
        'docs_json' => 'api-docs.json',
        'docs_yaml' => 'api-docs.yaml',
        'annotations' => [
            base_path('app'),
        ],
        'views' => base_path('resources/views/vendor/l5-swagger'),
        'base_url' => env('APP_URL', 'http://localhost'),
    ],

    'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', true),

    'proxy' => env('L5_SWAGGER_PROXY', false),

    'additional_config_url' => env('L5_SWAGGER_ADDITIONAL_CONFIG_URL', null),

    'generate_yaml' => env('L5_SWAGGER_GENERATE_YAML', false),

    'swagger_versions' => [
        'v3',
    ],
];
