<?php

return [
    // Frontend keys (rendered by vue-i18n) use {brace} interpolation.
    'actions' => [
        'save' => 'Enregistrer les modifications',
        'cancel' => 'Annuler',
        'create' => 'Nouveau {resource}',
        'back' => 'Retour',
        'edit' => 'Modifier',
        'export' => 'Exporter',
        'import' => 'Importer',
    ],
    'rows' => [
        'view' => 'Voir',
        'edit' => 'Modifier',
        'delete' => 'Supprimer',
        'restore' => 'Restaurer',
        'force_delete' => 'Supprimer définitivement',
    ],
    'index' => [
        'empty' => 'Rien dans l\'enclos pour l\'instant.',
        'search' => 'Rechercher…',
    ],
    'confirm' => [
        'delete' => 'Supprimer {title} ?',
        'force_delete' => 'Supprimer définitivement {title} ?',
        'default' => 'Êtes-vous sûr ?',
    ],
    'notifications' => [
        'title' => 'Notifications',
        'mark_all' => 'Tout marquer comme lu',
        'empty' => 'Tout est calme.',
    ],

    // Backend keys (rendered by Laravel __()) use :colon interpolation.
    'flash' => [
        'created' => ':resource créé.',
        'updated' => ':resource mis à jour.',
        'deleted' => ':resource supprimé.',
        'restored' => ':resource restauré.',
        'force_deleted' => ':resource supprimé définitivement.',
        'imported' => ':created enregistrements importés (:skipped ignorés).',
    ],
];
