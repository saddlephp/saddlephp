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

            $searchable = $resource::makeTable()->searchableColumns();

            if ($searchable === []) {
                continue;
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
                continue;
            }

            $groups[] = [
                'label' => $resource::label(),
                'uriKey' => $resource::uriKey(),
                'results' => $records->map(fn (Model $record) => [
                    'id' => $record->getKey(),
                    'title' => $resource::recordTitle($record),
                    'url' => '/'.$path.'/resources/'.$resource::uriKey().'/'.$record->getKey(),
                ])->all(),
            ];
        }

        return response()->json(['query' => $term, 'groups' => $groups]);
    }
}
