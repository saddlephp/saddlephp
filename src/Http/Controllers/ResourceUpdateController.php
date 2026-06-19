<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use SaddlePHP\Saddle;

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

        $indexUrl = '/'.app(Saddle::class)->path().'/resources/'.$resource::uriKey();

        return redirect()->to($indexUrl)
            ->with('success', __('saddle::panel.flash.updated', ['resource' => $resource::singularLabel()]));
    }
}
