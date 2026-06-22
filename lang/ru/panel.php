<?php

return [
    // Frontend keys (rendered by vue-i18n) use {brace} interpolation.
    'actions' => [
        'save' => 'Сохранить изменения',
        'cancel' => 'Отмена',
        'create' => 'Создать {resource}',
        'back' => 'Назад',
        'edit' => 'Изменить',
        'export' => 'Экспорт',
        'import' => 'Импорт',
    ],
    'rows' => [
        'view' => 'Просмотр',
        'edit' => 'Изменить',
        'delete' => 'Удалить',
        'restore' => 'Восстановить',
        'force_delete' => 'Удалить навсегда',
    ],
    'index' => [
        'empty' => 'В загоне пока пусто.',
        'search' => 'Поиск…',
    ],
    'confirm' => [
        'delete' => 'Удалить {title}?',
        'force_delete' => 'Удалить {title} навсегда?',
        'default' => 'Вы уверены?',
    ],
    'notifications' => [
        'title' => 'Уведомления',
        'mark_all' => 'Отметить все как прочитанные',
        'empty' => 'Всё спокойно.',
    ],

    // Backend keys (rendered by Laravel __()) use :colon interpolation.
    'flash' => [
        'created' => ':resource создан.',
        'updated' => ':resource обновлён.',
        'deleted' => ':resource удалён.',
        'restored' => ':resource восстановлен.',
        'force_deleted' => ':resource удалён навсегда.',
        'imported' => 'Импортировано записей: :created (пропущено: :skipped).',
    ],
];
