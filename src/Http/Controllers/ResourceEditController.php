<?php

declare(strict_types=1);

namespace RodeoPHP\Http\Controllers;

class ResourceEditController extends Controller
{
    public function __invoke(): never
    {
        abort(501);
    }
}
