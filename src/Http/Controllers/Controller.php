<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use SaddlePHP\RelationManager;
use SaddlePHP\Resource;
use SaddlePHP\Saddle;

abstract class Controller
{
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
