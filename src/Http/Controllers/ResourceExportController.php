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

        // Import is capped; export was not, so a large table was a synchronous
        // full dump that pinned a worker until it timed out.
        $maxRows = (int) config('saddle.export.max_rows', 50000);

        if ($maxRows > 0) {
            $query->limit($maxRows);
        }

        return response()->streamDownload(function () use ($query, $columns, $headers) {
            $out = fopen('php://output', 'w');

            // Excel on Windows reads a BOM-less file as ANSI and mojibakes every
            // non-ASCII character.
            fwrite($out, "\u{FEFF}");

            // escape: '' emits RFC-4180 CSV (no proprietary backslash escaping).
            // The default escape corrupts backslash/quote cells and can defeat
            // Csv::neutralize(); it is also deprecated as of PHP 8.4.
            fputcsv($out, $headers, escape: '');

            // lazy(), not cursor(). Builder::cursor() never calls
            // eagerLoadRelations(), so every ->with() applied by
            // applyTableQuery() and Resource::query() was silently discarded and
            // each relation column lazy-loaded once per row. lazy() pages
            // through get(), which does eager load, and keeps the user's chosen
            // sort (unlike lazyById, which would override it).
            foreach ($query->lazy(1000) as $record) {
                /** @var Model $record */
                fputcsv(
                    $out,
                    array_map(fn ($column) => Csv::neutralize($column->resolve($record)), $columns),
                    escape: '',
                );
            }

            fclose($out);
        }, $resource::uriKey().'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
