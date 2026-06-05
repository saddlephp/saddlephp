<?php

use Illuminate\Support\Facades\Route;
use SaddlePHP\Http\Controllers\DashboardController;
use SaddlePHP\Http\Controllers\ResourceCreateController;
use SaddlePHP\Http\Controllers\ResourceDestroyController;
use SaddlePHP\Http\Controllers\ResourceEditController;
use SaddlePHP\Http\Controllers\ResourceIndexController;
use SaddlePHP\Http\Controllers\ResourceOptionsController;
use SaddlePHP\Http\Controllers\ResourceStoreController;
use SaddlePHP\Http\Controllers\ResourceUpdateController;

Route::get('/', DashboardController::class)->name('dashboard');

// 'create' and 'options' are static path segments owned by the panel. The
// constraint below ensures the {record} placeholder can never capture those
// words, so static routes keep precedence even if their order ever changes.
$recordKey = '^(?!create$|options$).+$';

Route::get('/resources/{resourceKey}', ResourceIndexController::class)->name('resources.index');
Route::get('/resources/{resourceKey}/options/{field}', ResourceOptionsController::class)->name('resources.options');
Route::get('/resources/{resourceKey}/create', ResourceCreateController::class)->name('resources.create');
Route::post('/resources/{resourceKey}', ResourceStoreController::class)->name('resources.store');
Route::get('/resources/{resourceKey}/{record}/edit', ResourceEditController::class)->name('resources.edit')->where('record', $recordKey);
Route::put('/resources/{resourceKey}/{record}', ResourceUpdateController::class)->name('resources.update')->where('record', $recordKey);
Route::delete('/resources/{resourceKey}/{record}', ResourceDestroyController::class)->name('resources.destroy')->where('record', $recordKey);
