<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LogicException;
use SaddlePHP\Actions\Action;

class ResourceActionController extends Controller
{
    public function __invoke(Request $request, string $resourceKey, string $action): RedirectResponse
    {
        $resource = $this->resolveResource($resourceKey);
        abort_unless($resource::allows('viewAny'), 403);

        // Look up the declared action by name across both surfaces. Row actions
        // win the name first; only then do we consult bulk actions. The "kind"
        // decides the payload shape and how records are resolved.
        $declared = collect($resource::actions())
            ->first(fn (Action $candidate) => $candidate->name() === $action);
        $isBulk = false;

        if ($declared === null) {
            $declared = collect($resource::bulkActions())
                ->first(fn (Action $candidate) => $candidate->name() === $action);
            $isBulk = true;
        }

        // Unknown action: same info-hiding 404 as an unknown field/record.
        abort_if($declared === null, 404);

        $target = $isBulk
            ? $this->resolveBulkRecords($request, $resource, $declared)
            : $this->resolveRowRecord($request, $resource, $declared);

        // A declared action with no handle() is a developer error, not a 500
        // for the user to puzzle over. Surface it loudly (same posture as the
        // CustomField tag guard).
        if ($declared->callback() === null) {
            throw new LogicException(sprintf(
                'Action [%s] on resource [%s] has no handler. Call handle() when declaring the action.',
                $declared->name(),
                $resource,
            ));
        }

        DB::transaction(fn () => ($declared->callback())($target));

        return back()->with('success', $declared->message());
    }

    /** @param class-string<\SaddlePHP\Resource> $resource */
    protected function resolveRowRecord(Request $request, string $resource, Action $action): Model
    {
        $validated = $request->validate(['record' => ['required']]);

        $model = $resource::query($request)->findOrFail($validated['record']);

        if ($action->ability() !== null) {
            abort_unless($resource::allows($action->ability(), $model), 403);
        }

        return $model;
    }

    /**
     * @param  class-string<\SaddlePHP\Resource>  $resource
     * @return Collection<int, Model>
     */
    protected function resolveBulkRecords(Request $request, string $resource, Action $action): Collection
    {
        $validated = $request->validate([
            'records' => ['required', 'array', 'min:1', 'max:100'],
            'records.*' => ['required'],
        ]);

        $ids = array_unique($validated['records']);

        $models = $resource::query($request)->whereKey($ids)->get();

        // All-or-nothing: if any requested id is missing from the scoped fetch
        // (deleted, foreign tenant, or never existed) the whole operation 404s
        // rather than silently applying to the subset that resolved.
        abort_if($models->count() !== count($ids), 404);

        if ($action->ability() !== null) {
            foreach ($models as $model) {
                abort_unless($resource::allows($action->ability(), $model), 403);
            }
        }

        return $models;
    }
}
