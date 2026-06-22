<?php

return [
    // Frontend keys (rendered by vue-i18n) use {brace} interpolation.
    'actions' => [
        'save' => 'Salva modifiche',
        'cancel' => 'Annulla',
        'create' => 'Nuovo {resource}',
        'back' => 'Indietro',
        'edit' => 'Modifica',
        'export' => 'Esporta',
        'import' => 'Importa',
    ],
    'rows' => [
        'view' => 'Visualizza',
        'edit' => 'Modifica',
        'delete' => 'Elimina',
        'restore' => 'Ripristina',
        'force_delete' => 'Elimina definitivamente',
    ],
    'index' => [
        'empty' => 'Ancora niente nel recinto.',
        'search' => 'Cerca…',
    ],
    'confirm' => [
        'delete' => 'Eliminare {title}?',
        'force_delete' => 'Eliminare definitivamente {title}?',
        'default' => 'Sei sicuro?',
    ],
    'notifications' => [
        'title' => 'Notifiche',
        'mark_all' => 'Segna tutto come letto',
        'empty' => 'Tutto tranquillo.',
    ],

    // Backend keys (rendered by Laravel __()) use :colon interpolation.
    'flash' => [
        'created' => ':resource creato.',
        'updated' => ':resource aggiornato.',
        'deleted' => ':resource eliminato.',
        'restored' => ':resource ripristinato.',
        'force_deleted' => ':resource eliminato definitivamente.',
        'imported' => 'Importati :created record (:skipped ignorati).',
    ],
];
