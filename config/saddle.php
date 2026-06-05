<?php

declare(strict_types=1);

return [
    'path' => 'admin',

    'middleware' => ['web', 'auth'],

    'resources' => [
        'path' => app_path('Saddle'),
        'namespace' => 'App\\Saddle',
    ],

    'per_page' => 25,

    'brand' => [
        'name' => 'Saddle',
        'accent' => '#d9501f',
    ],
];
