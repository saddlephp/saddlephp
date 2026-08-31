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
            ->through(function (Model $record) use ($resource, $table) {
                $trashed = $resource::usesSoftDeletes() && $record->trashed();

                return [
                    'id' => $record->getKey(),
                    'title' => $resource::recordTitle($record),
                    'trashed' => $trashed,
                    'cells' => $this->rowCells($table, $record),
                    // A row is either live or trashed, and the index template
                    // reads the two halves in mutually exclusive branches. All
                    // five used to be evaluated for every row, so two fifths of
                    // the answers were computed and thrown away -- 130 policy
                    // invocations for a 25-row page, each of which is a query
                    // for any policy that touches the database.
                    'can' => $trashed
                        ? [
                            'view' => false,
                            'update' => false,
                            'delete' => false,
                            'restore' => $resource::allows('restore', $record),
                            'forceDelete' => $resource::allows('forceDelete', $record),
                        ]
                        : [
                            'view' => $resource::allows('view', $record),
                            'update' => $resource::allows('update', $record),
                            'delete' => $resource::allows('delete', $record),
                            'restore' => false,
                            'forceDelete' => false,
                        ],
                ];
            });

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
