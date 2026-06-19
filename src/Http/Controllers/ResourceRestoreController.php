<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use SaddlePHP\Saddle;

class ResourceRestoreController extends Controller
{
    public function __invoke(Request $request, string $resourceKey, string $record): RedirectResponse
    {
        $resource = $this->resolveResource($resourceKey);
        $model = $this->resolveTrashedRecord($request, $resource, $record);
        abort_unless($resource::allows('restore', $model), 403);

        $model->restore();

        $indexUrl = '/'.app(Saddle::class)->path().'/resources/'.$resource::uriKey();

        return redirect()->to($indexUrl)->with('success', __('saddle::panel.flash.restored', ['resource' => $resource::singularLabel()]));
    }
}
