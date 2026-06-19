<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResourceIndexController extends Controller
{
    public function __invoke(Request $request, string $resourceKey): Response
    {
        $resource = $this->resolveResource($resourceKey);
        abort_unless($resource::allows('viewAny'), 403);

        $table = $this->makeIndexTable($resource);
        $query = $resource::query($request);
        $state = $this->applyTableQuery($query, $table, $request);

        $rows = $query
            ->paginate((int) config('saddle.per_page', 25))
            ->withQueryString()
            ->through(fn (Model $record) => [
                'id' => $record->getKey(),
                'title' => $resource::recordTitle($record),
                'trashed' => $resource::usesSoftDeletes() && $record->trashed(),
                'cells' => collect($table->getColumns())
                    ->mapWithKeys(fn ($column) => [$column->name() => $column->resolve($record)])
                    ->all(),
                'can' => [
                    'view' => $resource::allows('view', $record),
                    'update' => $resource::allows('update', $record),
                    'delete' => $resource::allows('delete', $record),
                    'restore' => $resource::usesSoftDeletes() && $resource::allows('restore', $record),
                    'forceDelete' => $resource::usesSoftDeletes() && $resource::allows('forceDelete', $record),
                ],
            ]);

        return Inertia::render('Resources/Index', [
            'resource' => [
                'uriKey' => $resource::uriKey(),
                'label' => $resource::label(),
                'singularLabel' => $resource::singularLabel(),
                'canCreate' => $resource::allows('create'),
                'canExport' => $resource::allows('viewAny'),
                'canImport' => $resource::allows('create'),
            ],
            'columns' => $table->toInertia(),
            'filters' => $table->filtersToInertia(),
            'actions' => collect($resource::actions())->map->toArray()->values()->all(),
            'bulkActions' => collect($resource::bulkActions())->map->toArray()->values()->all(),
            'rows' => $rows,
            'query' => $state,
        ]);
    }
}
