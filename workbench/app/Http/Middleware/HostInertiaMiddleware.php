<?php

declare(strict_types=1);

namespace Workbench\App\Http\Middleware;

use Inertia\Middleware;

class HostInertiaMiddleware extends Middleware
{
    protected $rootView = 'host-app';
}
