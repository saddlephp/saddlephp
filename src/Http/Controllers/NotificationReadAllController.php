<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationReadAllController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        // One bulk UPDATE, rather than hydrating every unread notification and
        // saving each in turn (which is O(unread) queries).
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}
