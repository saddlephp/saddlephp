<?php

return [
    // Frontend keys (rendered by vue-i18n) use {brace} interpolation.
    'actions' => [
        'save' => 'Save changes',
        'cancel' => 'Cancel',
        'create' => 'New {resource}',
        'back' => 'Back',
        'edit' => 'Edit',
        'export' => 'Export',
        'import' => 'Import',
    ],
    'rows' => [
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'restore' => 'Restore',
        'force_delete' => 'Delete permanently',
    ],
    'index' => [
        'empty' => 'Nothing in the corral yet.',
        'search' => 'Search…',
    ],
    'confirm' => [
        'delete' => 'Delete {title}?',
        'force_delete' => 'Permanently delete {title}?',
        'default' => 'Are you sure?',
    ],
    'notifications' => [
        'title' => 'Notifications',
        'mark_all' => 'Mark all read',
        'empty' => 'All quiet.',
    ],

    // Backend keys (rendered by Laravel __()) use :colon interpolation.
    'flash' => [
        'created' => ':resource created.',
        'updated' => ':resource updated.',
        'deleted' => ':resource deleted.',
        'restored' => ':resource restored.',
        'force_deleted' => ':resource permanently deleted.',
        'imported' => 'Imported :created records (:skipped skipped).',
    ],
];
