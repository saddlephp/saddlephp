<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResourceCreateController extends Controller
{
    public function __invoke(Request $request, string $resourceKey): Response
    {
        $resource = $this->resolveResource($resourceKey);
        abort_unless($resource::allows('create'), 403);

        return Inertia::render('Resources/Create', [
            'resource' => [
                'uriKey' => $resource::uriKey(),
                'label' => $resource::label(),
                'singularLabel' => $resource::singularLabel(),
            ],
            'fields' => $resource::makeForm()->toInertia(),
        ]);
    }
}
