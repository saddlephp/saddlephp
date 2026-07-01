<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RelationStoreController extends Controller
{
    public function __invoke(Request $request, string $resourceKey, string $record, string $relation): RedirectResponse
    {
        $resource = $this->resolveResource($resourceKey);
        $parent = $this->resolveRecord($request, $resource, $record);
        abort_unless($resource::allows('view', $parent), 403);

        $manager = $this->resolveRelationManager($resource, $relation);
        abort_unless($manager::allows($parent, 'create'), 403);

        $form = $manager::makeForm($parent);
        $validated = $request->validate($form->rules());

        $related = $manager::newRelatedFor($parent); // foreign key already set
        $form->fill($related, $validated);
        $related->save();

        return back()->with('success', __('saddle::panel.flash.created', ['resource' => $manager::singularLabel()]));
    }
}
