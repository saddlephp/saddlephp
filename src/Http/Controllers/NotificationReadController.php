<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationReadController extends Controller
{
    public function __invoke(Request $request, string $notification): RedirectResponse
    {
        // Scoped to the current user — another user's id 404s.
        $request->user()->notifications()->findOrFail($notification)->markAsRead();

        return back();
    }
}
