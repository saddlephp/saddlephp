<?php

return [
    // Frontend keys (rendered by vue-i18n) use {brace} interpolation.
    'actions' => [
        'save' => '保存更改',
        'cancel' => '取消',
        'create' => '新建{resource}',
        'back' => '返回',
        'edit' => '编辑',
        'export' => '导出',
        'import' => '导入',
    ],
    'rows' => [
        'view' => '查看',
        'edit' => '编辑',
        'delete' => '删除',
        'restore' => '恢复',
        'force_delete' => '永久删除',
    ],
    'index' => [
        'empty' => '围栏里还空着。',
        'search' => '搜索…',
    ],
    'confirm' => [
        'delete' => '删除{title}？',
        'force_delete' => '永久删除{title}？',
        'default' => '确定吗？',
    ],
    'notifications' => [
        'title' => '通知',
        'mark_all' => '全部标记为已读',
        'empty' => '一切平静。',
    ],

    // Backend keys (rendered by Laravel __()) use :colon interpolation.
    'flash' => [
        'created' => '已创建:resource。',
        'updated' => '已更新:resource。',
        'deleted' => '已删除:resource。',
        'restored' => '已恢复:resource。',
        'force_deleted' => '已永久删除:resource。',
        'imported' => '已导入 :created 条记录（跳过 :skipped 条）。',
    ],
];
