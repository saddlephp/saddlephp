<?php

declare(strict_types=1);

namespace SaddlePHP\Tables;

use Illuminate\Http\Request;
use SaddlePHP\Tables\Columns\Column;
use SaddlePHP\Tables\Filters\Filter;

class Table
{
    /** @var array<int, Column> */
    protected array $columns = [];

    public static function make(): self
    {
        return new self;
    }

    /** @param array<int, Column> $columns */
    public function columns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Every column as declared, ignoring `Column::canSee()`.
     *
     * Only for introspection that must see the whole table. Anything that
     * reaches a response -- cells, payload, export, sort and search lists --
     * must go through the filtered accessors below instead.
     *
     * @return array<int, Column>
     */
    public function allColumns(): array
    {
        return $this->columns;
    }

    /**
     * The columns this request is allowed to see.
     *
     * Defaults to the current request so that every caller is filtered unless
     * it deliberately asks for `allColumns()`. The gate is fail-closed by
     * default in the sense that adding a new consumer cannot accidentally
     * bypass it.
     *
     * @return array<int, Column>
     */
    public function visibleColumns(?Request $request = null): array
    {
        $request ??= request();

        return array_values(array_filter(
            $this->columns,
            fn (Column $column) => $column->visibleTo($request),
        ));
    }

    /** @return array<int, Column> */
    public function getColumns(?Request $request = null): array
    {
        return $this->visibleColumns($request);
    }

    /** @return array<int, string> */
    public function sortableColumns(?Request $request = null): array
    {
        return collect($this->visibleColumns($request))->filter->isSortable()->map->name()->values()->all();
    }

    /** @return array<int, string> */
    public function searchableColumns(?Request $request = null): array
    {
        return collect($this->visibleColumns($request))->filter->isSearchable()->map->name()->values()->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function toInertia(?Request $request = null): array
    {
        return collect($this->visibleColumns($request))->map->toArray()->values()->all();
    }

    /** @var array<int, Filter> */
    protected array $filters = [];

    /** @param array<int, Filter> $filters */
    public function filters(array $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    /** @return array<int, Filter> */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /** @return array<int, array<string, mixed>> */
    public function filtersToInertia(): array
    {
        return collect($this->filters)->map->toArray()->values()->all();
    }
}
