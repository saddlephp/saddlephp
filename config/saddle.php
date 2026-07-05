<?php

declare(strict_types=1);

return [
    'path' => 'admin', // Change this to make your site more secure! if you want :)

    'middleware' => ['web', 'auth'],

    'resources' => [
        'path' => app_path('Saddle'),
        'namespace' => 'App\\Saddle',
    ],

    'per_page' => 25,

    // Maximum results returned per resource by global search.
    'global_search' => [
        'per_resource' => 5,
    ],

    // Where auto-discovered dashboard widgets live.
    'widgets' => [
        'path' => app_path('Saddle/Widgets'),
        'namespace' => 'App\\Saddle\\Widgets',
    ],

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

        // Optional invokable run after a tenant resolves; return a Response
        // (e.g. a redirect to billing) to deny access. null = no gate.
        'gate' => null,

        // Optional RegistersTenants implementation enabling /{path}/register.
        // null = tenant registration disabled.
        'registration' => null,
    ],

    /*
     * Authorization posture for resources that have no registered policy.
     *
     * Saddle is fail-closed by default: a resource without a registered policy
     * denies every ability (403), so a forgotten policy can never silently
     * expose data to every authenticated panel user. Register a policy for each
     * resource's model to grant access. Resources that DO register a policy are
     * unaffected either way.
     *
     * Set 'require_policy' to false to opt into the fail-open convention, where
     * a resource without a policy grants full CRUD to any authenticated user.
     * Only do this on panels whose guard is exclusively administrators.
     */
    'authorization' => [
        'require_policy' => true,
    ],

    // Default storage disk and directory for FileUpload fields (per-field overridable).
    'uploads' => [
        'disk' => 'public',
        'directory' => 'saddle',
    ],

    /*
     * CSV import runs synchronously in the web request, so cap the number of
     * rows a single upload may contain. A file over the cap is rejected whole
     * (the import is transactional) rather than processed partially.
     */
    'import' => [
        'max_rows' => 5000,
    ],
];
