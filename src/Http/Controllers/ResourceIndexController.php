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

        $table = $resource::makeTable();
        $query = $resource::query($request);

        $search = trim((string) $request->query('search', ''));
        $searchable = $table->searchableColumns();

        if ($search !== '' && $searchable !== []) {
            $query->where(function ($q) use ($search, $searchable) {
                foreach ($searchable as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        $requested = $request->query('filter', []);
        $requested = is_array($requested) ? $requested : [];
        $activeFilters = [];

        foreach ($table->getFilters() as $filter) {
            $value = $requested[$filter->name()] ?? null;

            if (is_string($value) && $value !== '' && $filter->accepts($value)) {
                $filter->apply($query, $value);
                $activeFilters[$filter->name()] = $value;
            }
        }

        $requestedSort = (string) $request->query('sort', '');

        if (in_array($requestedSort, $table->sortableColumns(), true)) {
            $sort = $requestedSort;
            $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';
        } else {
            $sort = $resource::newModel()->getKeyName();
            $direction = 'desc';
        }

        $query->orderBy($sort, $direction);

        $rows = $query
            ->paginate((int) config('saddle.per_page', 25))
            ->withQueryString()
            ->through(fn (Model $record) => [
                'id' => $record->getKey(),
                'title' => $resource::recordTitle($record),
                'cells' => collect($table->getColumns())
                    ->mapWithKeys(fn ($column) => [$column->name() => $column->resolve($record)])
                    ->all(),
                'can' => [
                    'update' => $resource::allows('update', $record),
                    'delete' => $resource::allows('delete', $record),
                ],
            ]);

        return Inertia::render('Resources/Index', [
            'resource' => [
                'uriKey' => $resource::uriKey(),
                'label' => $resource::label(),
                'singularLabel' => $resource::singularLabel(),
                'canCreate' => $resource::allows('create'),
            ],
            'columns' => $table->toInertia(),
            'filters' => $table->filtersToInertia(),
            'rows' => $rows,
            'query' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'filter' => $activeFilters,
            ],
        ]);
    }
}
