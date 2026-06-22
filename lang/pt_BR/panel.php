<?php

return [
    // Frontend keys (rendered by vue-i18n) use {brace} interpolation.
    'actions' => [
        'save' => 'Salvar alterações',
        'cancel' => 'Cancelar',
        'create' => 'Novo {resource}',
        'back' => 'Voltar',
        'edit' => 'Editar',
        'export' => 'Exportar',
        'import' => 'Importar',
    ],
    'rows' => [
        'view' => 'Ver',
        'edit' => 'Editar',
        'delete' => 'Excluir',
        'restore' => 'Restaurar',
        'force_delete' => 'Excluir permanentemente',
    ],
    'index' => [
        'empty' => 'Ainda não há nada no curral.',
        'search' => 'Buscar…',
    ],
    'confirm' => [
        'delete' => 'Excluir {title}?',
        'force_delete' => 'Excluir permanentemente {title}?',
        'default' => 'Tem certeza?',
    ],
    'notifications' => [
        'title' => 'Notificações',
        'mark_all' => 'Marcar tudo como lido',
        'empty' => 'Tudo tranquilo.',
    ],

    // Backend keys (rendered by Laravel __()) use :colon interpolation.
    'flash' => [
        'created' => ':resource criado.',
        'updated' => ':resource atualizado.',
        'deleted' => ':resource excluído.',
        'restored' => ':resource restaurado.',
        'force_deleted' => ':resource excluído permanentemente.',
        'imported' => ':created registros importados (:skipped ignorados).',
    ],
];
