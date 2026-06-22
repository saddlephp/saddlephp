<?php

return [
    // Frontend keys (rendered by vue-i18n) use {brace} interpolation.
    'actions' => [
        'save' => 'Guardar cambios',
        'cancel' => 'Cancelar',
        'create' => 'Nuevo {resource}',
        'back' => 'Volver',
        'edit' => 'Editar',
        'export' => 'Exportar',
        'import' => 'Importar',
    ],
    'rows' => [
        'view' => 'Ver',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'restore' => 'Restaurar',
        'force_delete' => 'Eliminar permanentemente',
    ],
    'index' => [
        'empty' => 'Aún no hay nada en el corral.',
        'search' => 'Buscar…',
    ],
    'confirm' => [
        'delete' => '¿Eliminar {title}?',
        'force_delete' => '¿Eliminar permanentemente {title}?',
        'default' => '¿Estás seguro?',
    ],
    'notifications' => [
        'title' => 'Notificaciones',
        'mark_all' => 'Marcar todo como leído',
        'empty' => 'Todo en calma.',
    ],

    // Backend keys (rendered by Laravel __()) use :colon interpolation.
    'flash' => [
        'created' => ':resource creado.',
        'updated' => ':resource actualizado.',
        'deleted' => ':resource eliminado.',
        'restored' => ':resource restaurado.',
        'force_deleted' => ':resource eliminado permanentemente.',
        'imported' => 'Se importaron :created registros (:skipped omitidos).',
    ],
];
