<?php

declare(strict_types=1);

return [
    'path' => 'admin',

    'middleware' => ['web', 'auth'],

    'resources' => [
        'path' => app_path('Rodeo'),
        'namespace' => 'App\\Rodeo',
    ],

    'per_page' => 25,

    'brand' => [
        'name' => 'RodeoPHP',
        'accent' => '#d9501f',
    ],
];
