<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RelationEditController extends Controller
{
    public function __invoke(Request $request, string $resourceKey, string $record, string $relation, string $related): JsonResponse
    {
        $resource = $this->resolveResource($resourceKey);
        $parent = $this->resolveRecord($request, $resource, $record);
        abort_unless($resource::allows('view', $parent), 403);

        $manager = $this->resolveRelationManager($resource, $relation);
        // Scoped through the parent relationship: a record from another parent 404s.
        $model = $manager::relationFor($parent)->findOrFail($related);
        abort_unless($manager::allows($parent, 'update', $model), 403);

        return response()->json([
            'record' => ['id' => $model->getKey(), 'title' => $manager::recordTitle($model)],
            'fields' => $manager::makeForm($parent)->toInertia($model),
        ]);
    }
}
