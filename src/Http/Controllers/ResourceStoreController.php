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

        $record = $resource::newModel();
        $this->validateAndFill($request, $resource::makeForm(), $record);
        $this->stampTenant($resource, $record);
        $record->save();

        return $this->redirectToIndex($resource, 'created');
    }
}
