<?php

declare(strict_types=1);

return [
    'path' => 'admin', // Change this to make your site more secure! if you want :)

    'middleware' => ['web', 'auth'],

    'resources' => [
        'path' => app_path('Saddle'),
        'namespace' => 'App\\Saddle',

        /*
         * Scan the path above for resource classes at boot.
         *
         * Discovered resources and any passed to Saddle::register() are merged,
         * so a plugin registering its own resources no longer hides yours. Set
         * this to false to skip scanning and curate the list entirely by hand.
         */
        'discovery' => true,
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

        /* As with resources: discovered and registered widgets are merged. */
        'discovery' => true,
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

        /*
         * Ceiling in kilobytes applied to every FileUpload that does not set
         * its own maxSize(). Fields used to be unbounded unless the developer
         * remembered to cap them.
         */
        'max_size' => 10240,

        /*
         * Accepted types for a FileUpload with no image() or acceptedTypes()
         * constraint of its own, matched against the file's *content*.
         *
         * The default set omits html, svg, xml, js and the php family on
         * purpose. Laravel names a stored file from its detected content type,
         * so allowing those means a user can upload "notes.txt" full of HTML,
         * have it land as <random>.html on a public disk, and get script
         * execution on your application's own origin the moment an admin opens
         * the record. Leave empty to use the framework default set.
         */
        'allowed_extensions' => [],
    ],

    /*
     * CSV import runs synchronously in the web request, so cap the number of
     * rows a single upload may contain. A file over the cap is rejected whole
     * (the import is transactional) rather than processed partially.
     */
    'import' => [
        'max_rows' => 5000,
    ],

    'export' => [
        /*
         * Ceiling on rows written by a CSV export. Exports run synchronously on
         * the web request, so an uncapped one on a large table holds a worker
         * open until it times out and hands back a truncated file that still
         * looks like a success. Set to 0 to remove the cap.
         */
        'max_rows' => 50000,
    ],
];
