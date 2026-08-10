<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use SaddlePHP\Support\Csv;

class ResourceImportController extends Controller
{
    public function show(Request $request, string $resourceKey): Response
    {
        $resource = $this->resolveResource($resourceKey);
        abort_unless($resource::allows('create'), 403);

        return Inertia::render('Resources/Import', [
            'resource' => [
                'uriKey' => $resource::uriKey(),
                'label' => $resource::label(),
            ],
            // visibleFields, not fields: the page listed every field name
            // including ones canSee() hides from this user, advertising columns
            // they are not allowed to see. The write path already filtered.
            'fields' => collect($resource::makeForm()->visibleFields())
                ->map->name()->values()->all(),
        ]);
    }

    /**
     * Reduce a CSV header cell and a field name to a comparable key.
     *
     * "First Name", "first_name", and "  FIRST-NAME " all normalize to
     * "first name", which is what lets an exported file import back.
     */
    protected static function normalizeHeader(string $value): string
    {
        return trim(preg_replace('/[\s_-]+/', ' ', strtolower(trim($value))) ?? '');
    }

    public function store(Request $request, string $resourceKey): RedirectResponse
    {
        $resource = $this->resolveResource($resourceKey);
        abort_unless($resource::allows('create'), 403);

        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:10240']]);

        $form = $resource::makeForm();
        $rules = $form->rules();
        $fieldNames = collect($form->visibleFields())->map->name()->all();

        // Match headers on a normalized key so a file this panel exported can be
        // imported back. Export writes human labels (Str::headline), so a
        // "first_name" field goes out as "First Name" and used to match nothing
        // on the way back in -- every snake_case column was silently dropped,
        // and if one was required the whole file was skipped with no explanation.
        $lookup = [];

        foreach ($fieldNames as $name) {
            $lookup[self::normalizeHeader($name)] = $name;
            $lookup[self::normalizeHeader(Str::headline($name))] = $name;
        }

        $maxRows = (int) config('saddle.import.max_rows', 5000);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        abort_if($handle === false, 422, 'The import file could not be read.');

        try {
            $rawHeader = fgetcsv($handle, escape: '') ?: [];

            // Excel, Sheets, and Numbers all write a UTF-8 BOM. Left attached it
            // fuses onto the first header name, so the first column silently
            // fails to map.
            if (isset($rawHeader[0])) {
                $rawHeader[0] = Csv::stripBom($rawHeader[0]);
            }

            $header = array_map(fn ($h) => self::normalizeHeader((string) $h), $rawHeader);

            // Import atomically: a hard error or an over-cap file rolls back the
            // whole batch instead of leaving a partial import behind.
            [$created, $skipped] = DB::transaction(function () use ($handle, $header, $lookup, $rules, $resource, $maxRows, $form) {
                $created = 0;
                $skipped = 0;
                $rows = 0;

                while (($row = fgetcsv($handle, escape: '')) !== false) {
                    abort_if(++$rows > $maxRows, 422, "Import files are limited to {$maxRows} rows.");

                    // fgetcsv yields [null] for a blank line, which every file
                    // with a trailing newline has. Counting that as a skipped
                    // row made a clean import report "skipped: 1".
                    if ($row === [null]) {
                        $rows--;

                        continue;
                    }

                    $assoc = [];

                    foreach ($header as $i => $key) {
                        if (! isset($lookup[$key])) {
                            continue;
                        }

                        $value = $row[$i] ?? null;

                        // The row is assembled by hand, so Laravel's
                        // ConvertEmptyStringsToNull middleware never sees it. An
                        // empty cell reaching an integer or date column is a
                        // 22P02 on Postgres and a 1366 on MySQL strict -- which,
                        // inside this transaction, discards every good row that
                        // came before it. On SQLite it inserts '' and corrupts
                        // quietly instead.
                        $assoc[$lookup[$key]] = $value === '' ? null : Csv::denormalize($value);
                    }

                    $validator = Validator::make($assoc, $rules);

                    if ($validator->fails()) {
                        $skipped++;

                        continue;
                    }

                    $record = $resource::newModel();
                    $form->fill($record, $validator->validated());
                    $this->stampTenant($resource, $record);
                    $record->save();
                    $created++;
                }

                return [$created, $skipped];
            });
        } finally {
            fclose($handle);
        }

        return $this->redirectToIndex($resource, 'imported', ['created' => $created, 'skipped' => $skipped]);
    }
}
