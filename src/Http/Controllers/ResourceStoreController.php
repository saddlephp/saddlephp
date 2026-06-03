<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResourceStoreController extends Controller
{
    public function __invoke(Request $request, string $resourceKey): RedirectResponse
    {
        $resource = $this->resolveResource($resourceKey);
        abort_unless($resource::allows('create'), 403);

        $form = $resource::makeForm();
        $validated = $request->validate($form->rules());

        $record = $resource::newModel();
        $form->fill($record, $validated);
        $record->save();

        return redirect()
            ->route('saddle.resources.index', $resource::uriKey())
            ->with('success', $resource::singularLabel().' created.');
    }
}
