<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use SaddlePHP\Saddle;

class ResourceDestroyController extends Controller
{
    public function __invoke(Request $request, string $resourceKey, string $record): RedirectResponse
    {
        $resource = $this->resolveResource($resourceKey);
        $model = $this->resolveRecord($request, $resource, $record);
        abort_unless($resource::allows('delete', $model), 403);

        $model->delete();

        $indexUrl = '/'.app(Saddle::class)->path().'/resources/'.$resource::uriKey();

        return redirect()->to($indexUrl)
            ->with('success', __('saddle::panel.flash.deleted', ['resource' => $resource::singularLabel()]));
    }
}
