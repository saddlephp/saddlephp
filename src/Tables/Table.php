<?php

declare(strict_types=1);

namespace RodeoPHP\Tables;

use RodeoPHP\Tables\Columns\TextColumn;

class Table
{
    /** @var array<int, TextColumn> */
    protected array $columns = [];

    public static function make(): self
    {
        return new self;
    }

    /** @param array<int, TextColumn> $columns */
    public function columns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    /** @return array<int, TextColumn> */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /** @return array<int, string> */
    public function sortableColumns(): array
    {
        return collect($this->columns)->filter->isSortable()->map->name()->values()->all();
    }

    /** @return array<int, string> */
    public function searchableColumns(): array
    {
        return collect($this->columns)->filter->isSearchable()->map->name()->values()->all();
    }

    /** @return array<int, array{name: string, label: string, sortable: bool}> */
    public function toInertia(): array
    {
        return collect($this->columns)->map->toArray()->values()->all();
    }
}
