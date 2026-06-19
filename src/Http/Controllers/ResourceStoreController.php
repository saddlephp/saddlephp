<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use SaddlePHP\Saddle;

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

        $tenant = app(Saddle::class)->tenant();

        if ($resource::$tenant !== null && $tenant !== null) {
            $record->{$resource::$tenant}()->associate($tenant);
        }

        $record->save();

        $indexUrl = '/'.app(Saddle::class)->path().'/resources/'.$resource::uriKey();

        return redirect()->to($indexUrl)
            ->with('success', __('saddle::panel.flash.created', ['resource' => $resource::singularLabel()]));
    }
}
