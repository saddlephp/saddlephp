<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SaddlePHP\Forms\Form;
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
     * @param  class-string<\SaddlePHP\Resource>  $resource
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
        $query->with($this->relationColumnRoots($table));

        $search = trim((string) $request->query('search', ''));
        $this->applySearch($query, $table->searchableColumns(), $search);

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

    /**
     * Constrain a query by a search term across the given columns, escaping the
     * term so LIKE wildcards are matched literally. A no-op for an empty term or
     * no columns. Shared by the index/export and global search.
     *
     * @param  array<int, string>  $columns
     */
    protected function applySearch(Builder $query, array $columns, string $term): void
    {
        if ($term === '' || $columns === []) {
            return;
        }

        $pattern = '%'.Search::escapeLike($term).'%';

        $query->where(function (Builder $q) use ($columns, $pattern) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', $pattern);
            }
        });
    }

    /**
     * The relations behind a table's dot-path columns (e.g. "rider.name" yields
     * "rider", "rider.ranch.name" yields "rider.ranch"). Eager-loading these
     * before rendering avoids a lazy load per relation per row (N+1).
     *
     * @return array<int, string>
     */
    protected function relationColumnRoots(Table $table): array
    {
        return collect($table->getColumns())
            ->map(fn ($column) => $column->name())
            ->filter(fn (string $name) => str_contains($name, '.'))
            ->map(fn (string $name) => Str::beforeLast($name, '.'))
            ->unique()
            ->values()
            ->all();
    }

    /** @return class-string<\SaddlePHP\Resource> */
    protected function resolveResource(string $uriKey): string
    {
        return app(Saddle::class)->resourceFor($uriKey) ?? abort(404);
    }

    /** @param class-string<\SaddlePHP\Resource> $resource */
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
     * @param  class-string<\SaddlePHP\Resource>  $resource
     */
    protected function resolveTrashedRecord(Request $request, string $resource, string|int $recordId): Model
    {
        abort_unless($resource::usesSoftDeletes(), 404);

        return $resource::query($request)->withTrashed()->findOrFail($recordId);
    }

    /**
     * @param  class-string<\SaddlePHP\Resource>  $resource
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
            ->with($this->relationColumnRoots($table))
            ->paginate((int) config('saddle.per_page', 25))
            ->through(fn (Model $record) => [
                'id' => $record->getKey(),
                'title' => $manager::recordTitle($record),
                'cells' => $this->rowCells($table, $record),
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

    /**
     * Resolve every column's value for one record into a name => value map.
     *
     * @return array<string, mixed>
     */
    protected function rowCells(Table $table, Model $record): array
    {
        return collect($table->getColumns())
            ->mapWithKeys(fn ($column) => [$column->name() => $column->resolve($record)])
            ->all();
    }

    /**
     * Validate the request against a form's rules and fill the model with the
     * validated data. Shared by the resource and relation store/update paths.
     */
    protected function validateAndFill(Request $request, Form $form, Model $model): void
    {
        $form->fill($model, $request->validate($form->rules()));
    }

    /**
     * Stamp the current tenant onto a new record when the resource is
     * tenant-scoped and tenancy is active. A no-op otherwise.
     *
     * @param  class-string<\SaddlePHP\Resource>  $resource
     */
    protected function stampTenant(string $resource, Model $record): void
    {
        $tenant = app(Saddle::class)->tenant();

        if ($resource::$tenant !== null && $tenant !== null) {
            $record->{$resource::$tenant}()->associate($tenant);
        }
    }

    /**
     * The absolute URL of a resource's index (tenant prefix included).
     *
     * @param  class-string<\SaddlePHP\Resource>  $resource
     */
    protected function resourceIndexUrl(string $resource): string
    {
        return '/'.app(Saddle::class)->path().'/resources/'.$resource::uriKey();
    }

    /**
     * Redirect to a resource's index with a translated success flash.
     *
     * @param  class-string<\SaddlePHP\Resource>  $resource
     */
    protected function redirectToIndex(string $resource, string $flashKey): RedirectResponse
    {
        return redirect()->to($this->resourceIndexUrl($resource))
            ->with('success', __("saddle::panel.flash.$flashKey", ['resource' => $resource::singularLabel()]));
    }
}
