<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SaddlePHP\Saddle;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if ($term === '') {
            return response()->json(['query' => '', 'groups' => []]);
        }

        $path = app(Saddle::class)->path();
        $limit = (int) config('saddle.global_search.per_resource', 5);
        $groups = [];

        foreach (app(Saddle::class)->resources() as $resource) {
            if (! $resource::$globalSearch || ! $resource::allows('viewAny')) {
                continue;
            }

            // One misconfigured resource (e.g. a searchable column that throws)
            // must not take down the whole search — skip it and report, exactly
            // as Saddle::nav() builds each sidebar item defensively.
            $group = rescue(fn () => $this->groupFor($resource, $request, $term, $limit, $path), null, report: true);

            if ($group !== null) {
                $groups[] = $group;
            }
        }

        return response()->json(['query' => $term, 'groups' => $groups]);
    }

    /**
     * Search a single resource and return its result group, or null when it has
     * no searchable columns or no matches.
     *
     * @param  class-string<\SaddlePHP\Resource>  $resource
     * @return array<string, mixed>|null
     */
    protected function groupFor(string $resource, Request $request, string $term, int $limit, string $path): ?array
    {
        $searchable = $resource::makeTable()->searchableColumns();

        if ($searchable === []) {
            return null;
        }

        $records = $resource::query($request)
            ->where(function ($query) use ($searchable, $term) {
                foreach ($searchable as $column) {
                    $query->orWhere($column, 'like', "%{$term}%");
                }
            })
            ->limit($limit)
            ->get();

        if ($records->isEmpty()) {
            return null;
        }

        return [
            'label' => $resource::label(),
            'uriKey' => $resource::uriKey(),
            'results' => $records->map(fn (Model $record) => [
                'id' => $record->getKey(),
                'title' => $resource::recordTitle($record),
                'url' => '/'.$path.'/resources/'.$resource::uriKey().'/'.$record->getKey(),
            ])->all(),
        ];
    }
}
