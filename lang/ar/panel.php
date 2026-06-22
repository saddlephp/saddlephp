<?php

return [
    // Frontend keys (rendered by vue-i18n) use {brace} interpolation.
    'actions' => [
        'save' => 'حفظ التغييرات',
        'cancel' => 'إلغاء',
        'create' => '{resource} جديد',
        'back' => 'رجوع',
        'edit' => 'تعديل',
        'export' => 'تصدير',
        'import' => 'استيراد',
    ],
    'rows' => [
        'view' => 'عرض',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'restore' => 'استعادة',
        'force_delete' => 'حذف نهائي',
    ],
    'index' => [
        'empty' => 'لا شيء في الحظيرة بعد.',
        'search' => 'بحث…',
    ],
    'confirm' => [
        'delete' => 'حذف {title}؟',
        'force_delete' => 'حذف {title} نهائيًا؟',
        'default' => 'هل أنت متأكد؟',
    ],
    'notifications' => [
        'title' => 'الإشعارات',
        'mark_all' => 'تعليم الكل كمقروء',
        'empty' => 'كل شيء هادئ.',
    ],

    // Backend keys (rendered by Laravel __()) use :colon interpolation.
    'flash' => [
        'created' => 'تم إنشاء :resource.',
        'updated' => 'تم تحديث :resource.',
        'deleted' => 'تم حذف :resource.',
        'restored' => 'تمت استعادة :resource.',
        'force_deleted' => 'تم حذف :resource نهائيًا.',
        'imported' => 'تم استيراد :created سجلات (تم تخطي :skipped).',
    ],
];
