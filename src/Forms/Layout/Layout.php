<?php

declare(strict_types=1);

namespace SaddlePHP\Forms\Layout;

use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * A layout container groups child fields and nested containers for
 * presentation. Containers are never bound, validated, or filled themselves —
 * the owning Form walks them to reach the leaf fields and to emit a tree
 * payload. Hidden (canSee-false) leaves are excluded by the Form's serializer,
 * which is handed in so visibility stays a single source of truth.
 */
abstract class Layout
{
    /** @var array<int, mixed> Child fields and/or nested layout containers. */
    protected array $schema = [];

    /** @param array<int, mixed> $schema */
    public function schema(array $schema): static
    {
        $this->schema = $schema;

        return $this;
    }

    /** @return array<int, mixed> */
    public function children(): array
    {
        return $this->schema;
    }

    /** The payload discriminator the recursive renderer switches on. */
    abstract public function layout(): string;

    /**
     * Serialize this container to its tree node. The Form supplies a serializer
     * that turns each child (field or nested container) into its payload array
     * while filtering hidden leaves; containers never see hidden leaves.
     *
     * @param  Closure(array<int, mixed>): array<int, array<string, mixed>>  $serializeChildren
     * @return array<string, mixed>
     */
    public function toInertia(?Model $record, Closure $serializeChildren): array
    {
        return array_merge(
            ['layout' => $this->layout()],
            $this->meta(),
            ['schema' => $serializeChildren($this->children())],
        );
    }

    /** @return array<string, mixed> */
    protected function meta(): array
    {
        return [];
    }
}
