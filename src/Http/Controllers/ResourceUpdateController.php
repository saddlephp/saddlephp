<?php

declare(strict_types=1);

namespace RodeoPHP\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResourceUpdateController extends Controller
{
    public function __invoke(Request $request, string $resourceKey, string $record): RedirectResponse
    {
        $resource = $this->resolveResource($resourceKey);
        $model = $this->resolveRecord($request, $resource, $record);
        abort_unless($resource::allows('update', $model), 403);

        $form = $resource::makeForm();
        $validated = $request->validate($form->rules());

        $form->fill($model, $validated);
        $model->save();

        return redirect()
            ->route('rodeo.resources.index', $resource::uriKey())
            ->with('success', $resource::singularLabel().' updated.');
    }
}
