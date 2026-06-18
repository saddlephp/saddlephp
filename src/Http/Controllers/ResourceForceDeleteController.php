<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use SaddlePHP\Saddle;

class ResourceForceDeleteController extends Controller
{
    public function __invoke(Request $request, string $resourceKey, string $record): RedirectResponse
    {
        $resource = $this->resolveResource($resourceKey);
        $model = $this->resolveTrashedRecord($request, $resource, $record);
        abort_unless($resource::allows('forceDelete', $model), 403);

        $model->forceDelete();

        $indexUrl = '/'.app(Saddle::class)->path().'/resources/'.$resource::uriKey();

        return redirect()->to($indexUrl)->with('success', $resource::singularLabel().' permanently deleted.');
    }
}
