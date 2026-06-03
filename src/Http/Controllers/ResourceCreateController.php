<?php

declare(strict_types=1);

namespace RodeoPHP\Http\Controllers;

class ResourceCreateController extends Controller
{
    public function __invoke(): never
    {
        abort(501);
    }
}
