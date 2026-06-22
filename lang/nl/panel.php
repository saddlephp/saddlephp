<?php

return [
    // Frontend keys (rendered by vue-i18n) use {brace} interpolation.
    'actions' => [
        'save' => 'Wijzigingen opslaan',
        'cancel' => 'Annuleren',
        'create' => 'Nieuwe {resource}',
        'back' => 'Terug',
        'edit' => 'Bewerken',
        'export' => 'Exporteren',
        'import' => 'Importeren',
    ],
    'rows' => [
        'view' => 'Bekijken',
        'edit' => 'Bewerken',
        'delete' => 'Verwijderen',
        'restore' => 'Herstellen',
        'force_delete' => 'Permanent verwijderen',
    ],
    'index' => [
        'empty' => 'Nog niets in de kraal.',
        'search' => 'Zoeken…',
    ],
    'confirm' => [
        'delete' => '{title} verwijderen?',
        'force_delete' => '{title} permanent verwijderen?',
        'default' => 'Weet je het zeker?',
    ],
    'notifications' => [
        'title' => 'Meldingen',
        'mark_all' => 'Alles als gelezen markeren',
        'empty' => 'Alles rustig.',
    ],

    // Backend keys (rendered by Laravel __()) use :colon interpolation.
    'flash' => [
        'created' => ':resource aangemaakt.',
        'updated' => ':resource bijgewerkt.',
        'deleted' => ':resource verwijderd.',
        'restored' => ':resource hersteld.',
        'force_deleted' => ':resource permanent verwijderd.',
        'imported' => ':created records geïmporteerd (:skipped overgeslagen).',
    ],
];
