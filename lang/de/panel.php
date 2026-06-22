<?php

return [
    // Frontend keys (rendered by vue-i18n) use {brace} interpolation.
    'actions' => [
        'save' => 'Änderungen speichern',
        'cancel' => 'Abbrechen',
        'create' => 'Neu: {resource}',
        'back' => 'Zurück',
        'edit' => 'Bearbeiten',
        'export' => 'Exportieren',
        'import' => 'Importieren',
    ],
    'rows' => [
        'view' => 'Ansehen',
        'edit' => 'Bearbeiten',
        'delete' => 'Löschen',
        'restore' => 'Wiederherstellen',
        'force_delete' => 'Endgültig löschen',
    ],
    'index' => [
        'empty' => 'Noch nichts im Gehege.',
        'search' => 'Suchen…',
    ],
    'confirm' => [
        'delete' => '{title} löschen?',
        'force_delete' => '{title} endgültig löschen?',
        'default' => 'Sind Sie sicher?',
    ],
    'notifications' => [
        'title' => 'Benachrichtigungen',
        'mark_all' => 'Alle als gelesen markieren',
        'empty' => 'Alles ruhig.',
    ],

    // Backend keys (rendered by Laravel __()) use :colon interpolation.
    'flash' => [
        'created' => ':resource erstellt.',
        'updated' => ':resource aktualisiert.',
        'deleted' => ':resource gelöscht.',
        'restored' => ':resource wiederhergestellt.',
        'force_deleted' => ':resource endgültig gelöscht.',
        'imported' => ':created Datensätze importiert (:skipped übersprungen).',
    ],
];
