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

    /*
     * Opt-in multi-tenancy. Set 'model' to an Eloquent class to mount the
     * panel under /{path}/{tenant} and scope every data path to the resolved
     * tenant. 'relationship' is the tenant-side relation listing its members
     * (used for the membership check). null disables tenancy entirely, leaving
     * v0.5 behavior byte-identical. Changing this requires `php artisan
     * route:clear` because the {tenant} prefix is decided at boot.
     */
    'tenancy' => [
        'model' => null,
        'relationship' => 'users',
    ],

    /*
     * Authorization posture for resources that have no registered policy.
     *
     * By default Saddle is fail-open: a resource without a policy grants full
     * CRUD to any authenticated panel user. This keeps simple panels
     * frictionless. Set 'require_policy' to true to flip to fail-closed ,
     * resources without a policy then deny every ability (403), so a forgotten
     * policy can never silently expose data. Resources that DO register a
     * policy are unaffected either way.
     */
    'authorization' => [
        'require_policy' => false,
    ],
];
