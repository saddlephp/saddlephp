<?php

return [
    // Frontend keys (rendered by vue-i18n) use {brace} interpolation.
    'actions' => [
        'save' => 'Zapisz zmiany',
        'cancel' => 'Anuluj',
        'create' => 'Nowy {resource}',
        'back' => 'Wstecz',
        'edit' => 'Edytuj',
        'export' => 'Eksportuj',
        'import' => 'Importuj',
    ],
    'rows' => [
        'view' => 'Podgląd',
        'edit' => 'Edytuj',
        'delete' => 'Usuń',
        'restore' => 'Przywróć',
        'force_delete' => 'Usuń trwale',
    ],
    'index' => [
        'empty' => 'Na razie pusto w zagrodzie.',
        'search' => 'Szukaj…',
    ],
    'confirm' => [
        'delete' => 'Usunąć {title}?',
        'force_delete' => 'Trwale usunąć {title}?',
        'default' => 'Czy na pewno?',
    ],
    'notifications' => [
        'title' => 'Powiadomienia',
        'mark_all' => 'Oznacz wszystkie jako przeczytane',
        'empty' => 'Cisza i spokój.',
    ],

    // Backend keys (rendered by Laravel __()) use :colon interpolation.
    'flash' => [
        'created' => 'Utworzono :resource.',
        'updated' => 'Zaktualizowano :resource.',
        'deleted' => 'Usunięto :resource.',
        'restored' => 'Przywrócono :resource.',
        'force_deleted' => 'Trwale usunięto :resource.',
        'imported' => 'Zaimportowano :created rekordów (pominięto :skipped).',
    ],
];
