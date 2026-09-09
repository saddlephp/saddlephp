<?php

declare(strict_types=1);

namespace SaddlePHP\Tables\Columns;

use Illuminate\Database\Eloquent\Model;

class BooleanColumn extends Column
{
    protected string $type = 'boolean';

    public function resolve(Model $record): mixed
    {
        // Cast first, then format: the callback sees the boolean the panel
        // would otherwise have rendered, not the raw column value.
        return $this->format((bool) data_get($record, $this->name), $record);
    }
}
