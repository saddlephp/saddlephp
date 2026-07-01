<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use SaddlePHP\Saddle;

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
            'fields' => collect($resource::makeForm()->fields())->map->name()->values()->all(),
        ]);
    }

    public function store(Request $request, string $resourceKey): RedirectResponse
    {
        $resource = $this->resolveResource($resourceKey);
        abort_unless($resource::allows('create'), 403);

        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:10240']]);

        $form = $resource::makeForm();
        $rules = $form->rules();
        $fieldNames = collect($form->fields())->map->name()->all();
        $lowerNames = array_map('strtolower', $fieldNames);

        $maxRows = (int) config('saddle.import.max_rows', 5000);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        abort_if($handle === false, 422, 'The import file could not be read.');

        try {
            $header = array_map(fn ($h) => strtolower(trim((string) $h)), fgetcsv($handle) ?: []);
            $tenant = app(Saddle::class)->tenant();

            // Import atomically: a hard error or an over-cap file rolls back the
            // whole batch instead of leaving a partial import behind.
            [$created, $skipped] = DB::transaction(function () use ($handle, $header, $lowerNames, $fieldNames, $rules, $resource, $tenant, $maxRows, $form) {
                $created = 0;
                $skipped = 0;
                $rows = 0;

                while (($row = fgetcsv($handle)) !== false) {
                    abort_if(++$rows > $maxRows, 422, "Import files are limited to {$maxRows} rows.");

                    $assoc = [];

                    foreach ($header as $i => $key) {
                        $position = array_search($key, $lowerNames, true);

                        if ($position !== false) {
                            $assoc[$fieldNames[$position]] = $row[$i] ?? null;
                        }
                    }

                    $validator = Validator::make($assoc, $rules);

                    if ($validator->fails()) {
                        $skipped++;

                        continue;
                    }

                    $record = $resource::newModel();
                    $form->fill($record, $validator->validated());

                    if ($resource::$tenant !== null && $tenant !== null) {
                        $record->{$resource::$tenant}()->associate($tenant);
                    }

                    $record->save();
                    $created++;
                }

                return [$created, $skipped];
            });
        } finally {
            fclose($handle);
        }

        $indexUrl = '/'.app(Saddle::class)->path().'/resources/'.$resource::uriKey();

        return redirect()->to($indexUrl)
            ->with('success', __('saddle::panel.flash.imported', ['created' => $created, 'skipped' => $skipped]));
    }
}
