<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResourceUpdateController extends Controller
{
    public function __invoke(Request $request, string $resourceKey, string $record): RedirectResponse
    {
        $resource = $this->resolveResource($resourceKey);
        $model = $this->resolveRecord($request, $resource, $record);
        abort_unless($resource::allows('update', $model), 403);

        $this->validateAndFill($request, $resource::makeForm(), $model);
        $model->save();

        return $this->redirectToIndex($resource, 'updated');
    }
}
