<?php

declare(strict_types=1);

namespace SaddlePHP\Tables\Columns;

use Illuminate\Database\Eloquent\Model;

class TextColumn extends Column
{
    protected string $dateFormat = 'Y-m-d H:i';

    public function date(string $format = 'Y-m-d H:i'): static
    {
        $this->dateFormat = $format;

        return $this;
    }

    public function resolve(Model $record): mixed
    {
        $value = parent::resolve($record);

        return $value instanceof \DateTimeInterface ? $value->format($this->dateFormat) : $value;
    }
}
