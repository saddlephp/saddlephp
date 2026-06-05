<?php

declare(strict_types=1);

namespace SaddlePHP\Forms\Layout;

use Closure;
use Illuminate\Database\Eloquent\Model;

class Tab extends Layout
{
    final public function __construct(protected string $label) {}

    public static function make(string $label): static
    {
        return new static($label);
    }

    public function layout(): string
    {
        return 'tab';
    }

    /**
     * A tab serializes inside its parent Tabs node, so it emits its label and
     * children directly without a `layout` discriminator of its own.
     *
     * @param  Closure(array<int, mixed>): array<int, array<string, mixed>>  $serializeChildren
     * @return array<string, mixed>
     */
    public function toInertia(?Model $record, Closure $serializeChildren): array
    {
        return [
            'label' => $this->label,
            'schema' => $serializeChildren($this->children()),
        ];
    }
}
