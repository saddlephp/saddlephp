<?php

declare(strict_types=1);

namespace SaddlePHP\Forms\Layout;

use Closure;
use Illuminate\Database\Eloquent\Model;

class Tabs extends Layout
{
    /** @param array<int, Tab> $tabs */
    final public function __construct(array $tabs = [])
    {
        $this->schema = $tabs;
    }

    /** @param array<int, Tab> $tabs */
    public static function make(array $tabs): static
    {
        return new static($tabs);
    }

    public function layout(): string
    {
        return 'tabs';
    }

    /**
     * Tabs nest their children under a `tabs` key rather than `schema`; each
     * Tab serializes itself (label + its own filtered schema). The Tabs node
     * carries no flat `schema` of its own.
     *
     * @param  Closure(array<int, mixed>): array<int, array<string, mixed>>  $serializeChildren
     * @return array<string, mixed>
     */
    public function toInertia(?Model $record, Closure $serializeChildren): array
    {
        return [
            'layout' => $this->layout(),
            'tabs' => array_map(
                fn (Tab $tab) => $tab->toInertia($record, $serializeChildren),
                $this->children(),
            ),
        ];
    }
}
