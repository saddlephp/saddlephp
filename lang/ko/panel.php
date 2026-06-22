<?php

return [
    // Frontend keys (rendered by vue-i18n) use {brace} interpolation.
    'actions' => [
        'save' => '변경 사항 저장',
        'cancel' => '취소',
        'create' => '새 {resource}',
        'back' => '뒤로',
        'edit' => '편집',
        'export' => '내보내기',
        'import' => '가져오기',
    ],
    'rows' => [
        'view' => '보기',
        'edit' => '편집',
        'delete' => '삭제',
        'restore' => '복원',
        'force_delete' => '영구 삭제',
    ],
    'index' => [
        'empty' => '아직 아무것도 없습니다.',
        'search' => '검색…',
    ],
    'confirm' => [
        'delete' => '{title}을(를) 삭제할까요?',
        'force_delete' => '{title}을(를) 영구 삭제할까요?',
        'default' => '확실합니까?',
    ],
    'notifications' => [
        'title' => '알림',
        'mark_all' => '모두 읽음으로 표시',
        'empty' => '조용합니다.',
    ],

    // Backend keys (rendered by Laravel __()) use :colon interpolation.
    'flash' => [
        'created' => ':resource이(가) 생성되었습니다.',
        'updated' => ':resource이(가) 업데이트되었습니다.',
        'deleted' => ':resource이(가) 삭제되었습니다.',
        'restored' => ':resource이(가) 복원되었습니다.',
        'force_deleted' => ':resource이(가) 영구 삭제되었습니다.',
        'imported' => ':created개의 레코드를 가져왔습니다(:skipped개 건너뜀).',
    ],
];
