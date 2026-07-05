<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RelationUpdateController extends Controller
{
    public function __invoke(Request $request, string $resourceKey, string $record, string $relation, string $related): RedirectResponse
    {
        $resource = $this->resolveResource($resourceKey);
        $parent = $this->resolveRecord($request, $resource, $record);
        abort_unless($resource::allows('view', $parent), 403);

        $manager = $this->resolveRelationManager($resource, $relation);
        $model = $manager::relationFor($parent)->findOrFail($related);
        abort_unless($manager::allows($parent, 'update', $model), 403);

        $this->validateAndFill($request, $manager::makeForm($parent), $model);
        $model->save();

        return back()->with('success', __('saddle::panel.flash.updated', ['resource' => $manager::singularLabel()]));
    }
}
