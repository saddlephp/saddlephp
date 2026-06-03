<?php

declare(strict_types=1);

namespace RodeoPHP\Tables\Columns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TextColumn
{
    protected ?string $label = null;

    protected bool $sortable = false;

    protected bool $searchable = false;

    final public function __construct(protected string $name) {}

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function resolve(Model $record): mixed
    {
        $value = data_get($record, $this->name);

        return $value instanceof \DateTimeInterface ? $value->format('Y-m-d H:i') : $value;
    }

    /** @return array{name: string, label: string, sortable: bool} */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label ?? Str::headline($this->name),
            'sortable' => $this->sortable,
        ];
    }
}
