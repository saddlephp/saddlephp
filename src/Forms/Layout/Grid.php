<?php

declare(strict_types=1);

namespace SaddlePHP\Forms\Layout;

class Grid extends Layout
{
    final public function __construct(protected int $columns = 2) {}

    public static function make(int $columns = 2): static
    {
        return new static($columns);
    }

    public function layout(): string
    {
        return 'grid';
    }

    /** @return array<string, mixed> */
    protected function meta(): array
    {
        return ['columns' => $this->columns];
    }
}
