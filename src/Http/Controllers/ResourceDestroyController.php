<?php

declare(strict_types=1);

namespace RodeoPHP\Http\Controllers;

class ResourceDestroyController extends Controller
{
    public function __invoke(): never
    {
        abort(501);
    }
}
