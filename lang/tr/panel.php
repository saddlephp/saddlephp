<?php

return [
    // Frontend keys (rendered by vue-i18n) use {brace} interpolation.
    'actions' => [
        'save' => 'Değişiklikleri kaydet',
        'cancel' => 'İptal',
        'create' => 'Yeni {resource}',
        'back' => 'Geri',
        'edit' => 'Düzenle',
        'export' => 'Dışa aktar',
        'import' => 'İçe aktar',
    ],
    'rows' => [
        'view' => 'Görüntüle',
        'edit' => 'Düzenle',
        'delete' => 'Sil',
        'restore' => 'Geri yükle',
        'force_delete' => 'Kalıcı olarak sil',
    ],
    'index' => [
        'empty' => 'Ağılda henüz bir şey yok.',
        'search' => 'Ara…',
    ],
    'confirm' => [
        'delete' => '{title} silinsin mi?',
        'force_delete' => '{title} kalıcı olarak silinsin mi?',
        'default' => 'Emin misiniz?',
    ],
    'notifications' => [
        'title' => 'Bildirimler',
        'mark_all' => 'Tümünü okundu işaretle',
        'empty' => 'Her şey sakin.',
    ],

    // Backend keys (rendered by Laravel __()) use :colon interpolation.
    'flash' => [
        'created' => ':resource oluşturuldu.',
        'updated' => ':resource güncellendi.',
        'deleted' => ':resource silindi.',
        'restored' => ':resource geri yüklendi.',
        'force_deleted' => ':resource kalıcı olarak silindi.',
        'imported' => ':created kayıt içe aktarıldı (:skipped atlandı).',
    ],
];
