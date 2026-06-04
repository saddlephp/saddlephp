<?php

declare(strict_types=1);

namespace SaddlePHP\Tables\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

abstract class Filter
{
    protected ?string $label = null;

    /** The payload discriminator the panel switches on. Subclasses MUST set this. */
    protected string $type;

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

    public function name(): string
    {
        return $this->name;
    }

    /** Whether apply() would constrain the query for this value. */
    abstract public function accepts(string $value): bool;

    /**
     * Constrain the query for a validated, non-empty request value. Values the
     * filter does not recognise must be a no-op.
     */
    abstract public function apply(Builder $query, string $value): void;

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_merge([
            'name' => $this->name,
            'label' => $this->label ?? Str::headline($this->name),
            'type' => $this->type,
        ], $this->meta());
    }

    /** @return array<string, mixed> */
    protected function meta(): array
    {
        return [];
    }
}
