<?php

declare(strict_types=1);

namespace RodeoPHP\Http\Controllers;

class ResourceUpdateController extends Controller
{
    public function __invoke(): never
    {
        abort(501);
    }
}
