<?php

return [
    // Frontend keys (rendered by vue-i18n) use {brace} interpolation.
    'actions' => [
        'save' => '変更を保存',
        'cancel' => 'キャンセル',
        'create' => '新規{resource}',
        'back' => '戻る',
        'edit' => '編集',
        'export' => 'エクスポート',
        'import' => 'インポート',
    ],
    'rows' => [
        'view' => '表示',
        'edit' => '編集',
        'delete' => '削除',
        'restore' => '復元',
        'force_delete' => '完全に削除',
    ],
    'index' => [
        'empty' => 'まだ何もありません。',
        'search' => '検索…',
    ],
    'confirm' => [
        'delete' => '{title}を削除しますか？',
        'force_delete' => '{title}を完全に削除しますか？',
        'default' => 'よろしいですか？',
    ],
    'notifications' => [
        'title' => '通知',
        'mark_all' => 'すべて既読にする',
        'empty' => '通知はありません。',
    ],

    // Backend keys (rendered by Laravel __()) use :colon interpolation.
    'flash' => [
        'created' => ':resourceを作成しました。',
        'updated' => ':resourceを更新しました。',
        'deleted' => ':resourceを削除しました。',
        'restored' => ':resourceを復元しました。',
        'force_deleted' => ':resourceを完全に削除しました。',
        'imported' => ':created件のレコードをインポートしました（:skipped件スキップ）。',
    ],
];
