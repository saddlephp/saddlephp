<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use SaddlePHP\RelationManager;
use SaddlePHP\Resource;
use SaddlePHP\Saddle;
use SaddlePHP\Support\Search;
use SaddlePHP\Tables\Filters\TrashedFilter;
use SaddlePHP\Tables\Table;

abstract class Controller
{
    /**
     * Build a resource's index table, injecting the trashed filter for
     * soft-deletable resources.
     *
     * @param  class-string<resource>  $resource
     */
    protected function makeIndexTable(string $resource): Table
    {
        $table = $resource::makeTable();

        if ($resource::usesSoftDeletes()) {
            $table->filters(array_merge($table->getFilters(), [TrashedFilter::make('trashed')->label('Status')]));
        }

        return $table;
    }

    /**
     * Apply the index's search, filters, and sort to a query, returning the
     * resolved state for the payload. Shared by the index and CSV export.
     *
     * @return array{search: string, sort: string, direction: string, filter: array<string, string>}
     */
    protected function applyTableQuery(Builder $query, Table $table, Request $request): array
    {
        $search = trim((string) $request->query('search', ''));
        $searchable = $table->searchableColumns();

        if ($search !== '' && $searchable !== []) {
            $query->where(function ($q) use ($search, $searchable) {
                foreach ($searchable as $column) {
                    $q->orWhere($column, 'like', '%'.Search::escapeLike($search).'%');
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
            $sort = $query->getModel()->getKeyName();
            $direction = 'desc';
        }

        $query->orderBy($sort, $direction);

        return ['search' => $search, 'sort' => $sort, 'direction' => $direction, 'filter' => $activeFilters];
    }

    /** @return class-string<resource> */
    protected function resolveResource(string $uriKey): string
    {
        return app(Saddle::class)->resourceFor($uriKey) ?? abort(404);
    }

    /** @param class-string<resource> $resource */
    protected function resolveRecord(Request $request, string $resource, string|int $recordId): Model
    {
        return $resource::query($request)->findOrFail($recordId);
    }

    /**
     * Resolve a record including trashed rows, for restore/force-delete. Guards
     * resources whose model is not soft-deletable with a 404. Tenant scope still
     * applies (withTrashed does not drop the tenant constraint), so a foreign
     * tenant's trashed record never resolves.
     *
     * @param  class-string<resource>  $resource
     */
    protected function resolveTrashedRecord(Request $request, string $resource, string|int $recordId): Model
    {
        abort_unless($resource::usesSoftDeletes(), 404);

        return $resource::query($request)->withTrashed()->findOrFail($recordId);
    }

    /**
     * @param  class-string<resource>  $resource
     * @return class-string<RelationManager>
     */
    protected function resolveRelationManager(string $resource, string $relationKey): string
    {
        foreach ($resource::relations() as $manager) {
            if ($manager::uriKey() === $relationKey) {
                return $manager;
            }
        }

        abort(404);
    }

    /**
     * Build the frontend payload for one relation manager: columns, the blank
     * create form, the create permission, and the first page of parent-scoped
     * rows. Shared by the view page and the relation list endpoint.
     *
     * @param  class-string<RelationManager>  $manager
     * @return array<string, mixed>
     */
    protected function relationPayload(string $manager, Model $parent): array
    {
        $table = $manager::makeTable();

        $rows = $manager::relationFor($parent)
            ->paginate((int) config('saddle.per_page', 25))
            ->through(fn (Model $record) => [
                'id' => $record->getKey(),
                'title' => $manager::recordTitle($record),
                'cells' => collect($table->getColumns())
                    ->mapWithKeys(fn ($column) => [$column->name() => $column->resolve($record)])
                    ->all(),
                'can' => [
                    'update' => $manager::allows($parent, 'update', $record),
                    'delete' => $manager::allows($parent, 'delete', $record),
                ],
            ]);

        return [
            'key' => $manager::uriKey(),
            'label' => $manager::label(),
            'columns' => $table->toInertia(),
            'createForm' => $manager::makeForm($parent)->toInertia(),
            'canCreate' => $manager::allows($parent, 'create'),
            'rows' => $rows,
        ];
    }
}
