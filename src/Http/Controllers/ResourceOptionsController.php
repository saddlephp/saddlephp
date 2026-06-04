<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SaddlePHP\Fields\BelongsTo;

class ResourceOptionsController extends Controller
{
    public function __invoke(Request $request, string $resourceKey, string $field): JsonResponse
    {
        $resource = $this->resolveResource($resourceKey);
        // The picker only feeds the create and edit forms. A fresh model stands
        // in for "may update some record" since no record is in scope here.
        abort_unless(
            $resource::allows('create') || $resource::allows('update', $resource::newModel()),
            403,
        );

        $match = collect($resource::makeForm()->fields())
            ->first(fn ($formField) => $formField instanceof BelongsTo && $formField->name() === $field);

        abort_if($match === null, 404);

        return response()->json([
            'options' => $match->searchOptions(trim((string) $request->query('search', ''))),
        ]);
    }
}
