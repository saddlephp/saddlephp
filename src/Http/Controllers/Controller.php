<?php

declare(strict_types=1);

namespace RodeoPHP\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use RodeoPHP\Resource;
use RodeoPHP\Rodeo;

abstract class Controller
{
    /** @return class-string<Resource> */
    protected function resolveResource(string $uriKey): string
    {
        return app(Rodeo::class)->resourceFor($uriKey) ?? abort(404);
    }

    /** @param class-string<Resource> $resource */
    protected function resolveRecord(Request $request, string $resource, string|int $recordId): Model
    {
        return $resource::query($request)->findOrFail($recordId);
    }
}
