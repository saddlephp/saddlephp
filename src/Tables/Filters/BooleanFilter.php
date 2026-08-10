<?php

declare(strict_types=1);

namespace SaddlePHP\Tables\Filters;

use Illuminate\Database\Eloquent\Builder;

class BooleanFilter extends Filter
{
    protected string $type = 'boolean';

    public function accepts(string $value): bool
    {
        return $value === '1' || $value === '0';
    }

    public function apply(Builder $query, string $value): void
    {
        if ($this->accepts($value)) {
            // Bind an int, not a bool. Plenty of schemas store flags as
            // smallint/integer rather than boolean, and Postgres refuses to
            // compare those to a bool ("operator does not exist: integer =
            // boolean"). MySQL and SQLite coerce either way, so binding a bool
            // worked everywhere the test suite runs and nowhere else.
            $query->where($this->name, (int) ($value === '1'));
        }
    }
}
