<?php

declare(strict_types=1);

namespace SaddlePHP\Actions;

/**
 * A bulk row action shown when one or more records are selected on the index.
 *
 * The handle Closure receives an Eloquent Collection of the selected records.
 * The authorize() ability is checked per record; any failure aborts the entire
 * operation with 403 before the handler runs (all-or-nothing, predictable).
 */
class BulkAction extends Action
{
    /**
     * Pre-built delete action: name "delete", label "Delete", color accent,
     * confirmation "Delete the selected records?", authorize("delete"), and
     * a handler that calls delete() on every record in the collection.
     */
    public static function delete(): static
    {
        return static::make('delete')
            ->label('Delete')
            ->color('accent')
            ->requiresConfirmation('Delete the selected records?')
            ->authorize('delete')
            ->handle(fn ($records) => $records->each->delete());
    }
}
