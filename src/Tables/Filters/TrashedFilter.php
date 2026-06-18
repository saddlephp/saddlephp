<?php

declare(strict_types=1);

namespace SaddlePHP\Tables\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Renders as a plain select (reusing the select-filter UI). Drives the
 * SoftDeletes scope: 'without' keeps the default (trashed hidden), 'with' adds
 * trashed, 'only' shows just trashed.
 */
class TrashedFilter extends Filter
{
    protected string $type = 'select';

    public function accepts(string $value): bool
    {
        return in_array($value, ['without', 'with', 'only'], true);
    }

    public function apply(Builder $query, string $value): void
    {
        match ($value) {
            'with' => $query->withTrashed(),
            'only' => $query->onlyTrashed(),
            default => null, // 'without' — the global scope already hides trashed
        };
    }

    protected function meta(): array
    {
        return [
            'options' => [
                ['value' => 'without', 'label' => 'Active'],
                ['value' => 'with', 'label' => 'With trashed'],
                ['value' => 'only', 'label' => 'Only trashed'],
            ],
        ];
    }
}
