<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResourceViewController extends Controller
{
    public function __invoke(Request $request, string $resourceKey, string $record): Response
    {
        $resource = $this->resolveResource($resourceKey);
        $model = $this->resolveRecord($request, $resource, $record);
        abort_unless($resource::allows('view', $model), 403);

        return Inertia::render('Resources/Show', [
            'resource' => [
                'uriKey' => $resource::uriKey(),
                'label' => $resource::label(),
                'singularLabel' => $resource::singularLabel(),
            ],
            'record' => [
                'id' => $model->getKey(),
                'title' => $resource::recordTitle($model),
                'can' => [
                    'update' => $resource::allows('update', $model),
                    'delete' => $resource::allows('delete', $model),
                ],
            ],
            'fields' => $resource::makeForm()->toDisplay($model),
            'relations' => collect($resource::relations())
                ->map(fn (string $manager) => $this->relationPayload($manager, $model))
                ->values()->all(),
        ]);
    }
}
