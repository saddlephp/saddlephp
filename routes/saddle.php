<?php

use Illuminate\Support\Facades\Route;
use SaddlePHP\Http\Controllers\DashboardController;
use SaddlePHP\Http\Controllers\RelationDestroyController;
use SaddlePHP\Http\Controllers\RelationEditController;
use SaddlePHP\Http\Controllers\RelationIndexController;
use SaddlePHP\Http\Controllers\RelationStoreController;
use SaddlePHP\Http\Controllers\RelationUpdateController;
use SaddlePHP\Http\Controllers\ResourceActionController;
use SaddlePHP\Http\Controllers\ResourceCreateController;
use SaddlePHP\Http\Controllers\ResourceDestroyController;
use SaddlePHP\Http\Controllers\ResourceEditController;
use SaddlePHP\Http\Controllers\ResourceIndexController;
use SaddlePHP\Http\Controllers\ResourceOptionsController;
use SaddlePHP\Http\Controllers\ResourceStoreController;
use SaddlePHP\Http\Controllers\ResourceUpdateController;
use SaddlePHP\Http\Controllers\ResourceViewController;

Route::get('/', DashboardController::class)->name('dashboard');

// 'create', 'options', and 'actions' are static path segments owned by the
// panel. The constraint below ensures the {record} placeholder can never
// capture those words, so static routes keep precedence even if their order
// ever changes. [^/]+ keeps {record} to a single segment so the slash-less
// view route (GET .../{record}) cannot swallow deeper paths like .../{record}/edit.
$recordKey = '^(?!create$|options$|actions$)[^/]+$';

// standard routes for resources
Route::get('/resources/{resourceKey}', ResourceIndexController::class)->name('resources.index');
Route::get('/resources/{resourceKey}/options/{field}', ResourceOptionsController::class)->name('resources.options');
Route::post('/resources/{resourceKey}/actions/{action}', ResourceActionController::class)->name('resources.actions.run');
Route::get('/resources/{resourceKey}/create', ResourceCreateController::class)->name('resources.create');
Route::post('/resources/{resourceKey}', ResourceStoreController::class)->name('resources.store');
Route::get('/resources/{resourceKey}/{record}', ResourceViewController::class)->name('resources.view')->where('record', $recordKey);
Route::get('/resources/{resourceKey}/{record}/edit', ResourceEditController::class)->name('resources.edit')->where('record', $recordKey);
Route::put('/resources/{resourceKey}/{record}', ResourceUpdateController::class)->name('resources.update')->where('record', $recordKey);
Route::delete('/resources/{resourceKey}/{record}', ResourceDestroyController::class)->name('resources.destroy')->where('record', $recordKey);

// Relation managers: nested under a parent record, scoped through its HasMany.
Route::get('/resources/{resourceKey}/{record}/relations/{relation}', RelationIndexController::class)->name('resources.relations.index')->where('record', $recordKey);
Route::post('/resources/{resourceKey}/{record}/relations/{relation}', RelationStoreController::class)->name('resources.relations.store')->where('record', $recordKey);
Route::get('/resources/{resourceKey}/{record}/relations/{relation}/{related}/edit', RelationEditController::class)->name('resources.relations.edit')->where('record', $recordKey);
Route::put('/resources/{resourceKey}/{record}/relations/{relation}/{related}', RelationUpdateController::class)->name('resources.relations.update')->where('record', $recordKey);
Route::delete('/resources/{resourceKey}/{record}/relations/{relation}/{related}', RelationDestroyController::class)->name('resources.relations.destroy')->where('record', $recordKey);
