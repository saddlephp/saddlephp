<?php

declare(strict_types=1);

namespace SaddlePHP\Tables\Columns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use LogicException;

abstract class Column
{
    protected ?string $label = null;

    protected bool $sortable = false;

    protected bool $searchable = false;

    protected string $type = 'text';

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
        if ($sortable) {
            $this->assertNotRelationColumn('sorted');
        }

        $this->sortable = $sortable;

        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        if ($searchable) {
            $this->assertNotRelationColumn('searched');
        }

        $this->searchable = $searchable;

        return $this;
    }

    /**
     * Relation (dot-path) columns read through a loaded relation in PHP and
     * have no real database column to target, so sorting or searching them
     * would later compile to invalid SQL and 500 the index. Fail loudly at
     * build time instead.
     */
    protected function assertNotRelationColumn(string $verb): void
    {
        if (str_contains($this->name, '.')) {
            throw new LogicException(
                "Column [{$this->name}] is a relation column and cannot be {$verb}. ".
                'Relation (dot-path) columns are not sortable or searchable yet.'
            );
        }
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
        return data_get($record, $this->name);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_merge([
            'name' => $this->name,
            'label' => $this->label ?? Str::headline($this->name),
            'sortable' => $this->sortable,
            'type' => $this->type,
        ], $this->meta());
    }

    /** @return array<string, mixed> */
    protected function meta(): array
    {
        return [];
    }
}
