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

        // Re-pin the tenant after filling. Form::fill() writes attributes
        // directly, bypassing $fillable, so a resource that exposes its tenant
        // relation as a field let an update move the record into another
        // tenant. Store and import already stamped; update did not.
        $this->stampTenant($resource, $model);

        $model->save();

        return $this->redirectToIndex($resource, 'updated');
    }
}
