<?php

declare(strict_types=1);

namespace RodeoPHP\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResourceEditController extends Controller
{
    public function __invoke(Request $request, string $resourceKey, string $record): Response
    {
        $resource = $this->resolveResource($resourceKey);
        $model = $this->resolveRecord($request, $resource, $record);
        abort_unless($resource::allows('update', $model), 403);

        return Inertia::render('Resources/Edit', [
            'resource' => [
                'uriKey' => $resource::uriKey(),
                'label' => $resource::label(),
                'singularLabel' => $resource::singularLabel(),
            ],
            'record' => [
                'id' => $model->getKey(),
                'title' => $resource::recordTitle($model),
            ],
            'fields' => $resource::makeForm()->toInertia($model),
        ]);
    }
}
