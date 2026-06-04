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
            $query->where($this->name, $value === '1');
        }
    }
}
