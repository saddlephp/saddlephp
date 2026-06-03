<?php

use Illuminate\Support\Facades\Route;
use RodeoPHP\Http\Controllers\DashboardController;
use RodeoPHP\Http\Controllers\ResourceCreateController;
use RodeoPHP\Http\Controllers\ResourceDestroyController;
use RodeoPHP\Http\Controllers\ResourceEditController;
use RodeoPHP\Http\Controllers\ResourceIndexController;
use RodeoPHP\Http\Controllers\ResourceStoreController;
use RodeoPHP\Http\Controllers\ResourceUpdateController;

Route::get('/', DashboardController::class)->name('dashboard');

Route::get('/resources/{resourceKey}', ResourceIndexController::class)->name('resources.index');
Route::get('/resources/{resourceKey}/create', ResourceCreateController::class)->name('resources.create');
Route::post('/resources/{resourceKey}', ResourceStoreController::class)->name('resources.store');
Route::get('/resources/{resourceKey}/{record}/edit', ResourceEditController::class)->name('resources.edit');
Route::put('/resources/{resourceKey}/{record}', ResourceUpdateController::class)->name('resources.update');
Route::delete('/resources/{resourceKey}/{record}', ResourceDestroyController::class)->name('resources.destroy');
