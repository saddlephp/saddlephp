<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RelationDestroyController extends Controller
{
    public function __invoke(Request $request, string $resourceKey, string $record, string $relation, string $related): RedirectResponse
    {
        $resource = $this->resolveResource($resourceKey);
        $parent = $this->resolveRecord($request, $resource, $record);
        abort_unless($resource::allows('view', $parent), 403);

        $manager = $this->resolveRelationManager($resource, $relation);
        $model = $manager::relationFor($parent)->findOrFail($related);
        abort_unless($manager::allows($parent, 'delete', $model), 403);

        $model->delete();

        return back()->with('success', __('saddle::panel.flash.deleted', ['resource' => $manager::singularLabel()]));
    }
}
