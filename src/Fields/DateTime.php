<?php

declare(strict_types=1);

namespace SaddlePHP\Fields;

use Illuminate\Database\Eloquent\Model;

class DateTime extends Field
{
    protected string $component = 'datetime-field';

    protected function typeRules(): array
    {
        return ['date'];
    }

    public function resolve(Model $record): mixed
    {
        $value = parent::resolve($record);

        return $value instanceof \DateTimeInterface ? $value->format('Y-m-d\TH:i') : $value;
    }
}
