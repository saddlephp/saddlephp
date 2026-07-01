<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use SaddlePHP\Support\Csv;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResourceExportController extends Controller
{
    public function __invoke(Request $request, string $resourceKey): StreamedResponse
    {
        $resource = $this->resolveResource($resourceKey);
        abort_unless($resource::allows('viewAny'), 403);

        $table = $this->makeIndexTable($resource);
        $query = $resource::query($request);
        $this->applyTableQuery($query, $table, $request);

        $columns = $table->getColumns();
        $headers = array_map(fn ($column) => $column->toArray()['label'], $columns);

        return response()->streamDownload(function () use ($query, $columns, $headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);

            $query->cursor()->each(function (Model $record) use ($out, $columns) {
                fputcsv($out, array_map(fn ($column) => Csv::neutralize($column->resolve($record)), $columns));
            });

            fclose($out);
        }, $resource::uriKey().'.csv', ['Content-Type' => 'text/csv']);
    }
}
