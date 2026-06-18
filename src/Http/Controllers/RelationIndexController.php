<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RelationIndexController extends Controller
{
    public function __invoke(Request $request, string $resourceKey, string $record, string $relation): JsonResponse
    {
        $resource = $this->resolveResource($resourceKey);
        $parent = $this->resolveRecord($request, $resource, $record);
        abort_unless($resource::allows('view', $parent), 403);

        $manager = $this->resolveRelationManager($resource, $relation);
        abort_unless($manager::allows($parent, 'viewAny'), 403);

        return response()->json($this->relationPayload($manager, $parent));
    }
}
