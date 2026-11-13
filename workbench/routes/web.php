<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Workbench\App\Models\User;

Route::get('/login', fn () => 'login')->name('login');

// Signs in the DemoSeeder user so the panel can be browsed and screenshotted.
// Only registered when SADDLE_DEMO=1; the workbench never ships with the package.
if (env('SADDLE_DEMO')) {
    Route::middleware('web')->get('/demo-login', function () {
        Auth::login(User::query()->firstOrFail());

        return redirect('/admin');
    });
}
