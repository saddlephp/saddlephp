<?php

declare(strict_types=1);

namespace SaddlePHP\Forms;

use Illuminate\Database\Eloquent\Model;
use SaddlePHP\Fields\Field;
use SaddlePHP\Forms\Layout\Layout;

class Form
{
    /** @var array<int, Field|Layout> Raw schema: leaf fields and/or layout containers. */
    protected array $schema = [];

    protected ?Model $model = null;

    protected bool $prepared = false;

    public static function make(): self
    {
        return new self;
    }

    /** @param array<int, Field|Layout> $fields */
    public function schema(array $fields): static
    {
        $this->schema = $fields;

        return $this;
    }

    /**
     * The leaf fields of the schema, depth-first in declaration order. Layout
     * containers are walked through; only fields are returned. This is the
     * public contract every consumer (rules, fill, validation) relies on.
     *
     * @return array<int, Field>
     */
    public function fields(): array
    {
        $this->prepare();

        return $this->leaves($this->schema);
    }

    /**
     * Flatten the schema tree to its leaf fields, depth-first.
     *
     * @param  array<int, Field|Layout>  $nodes
     * @return array<int, Field>
     */
    protected function leaves(array $nodes): array
    {
        $leaves = [];

        foreach ($nodes as $node) {
            if ($node instanceof Layout) {
                foreach ($this->leaves($node->children()) as $leaf) {
                    $leaves[] = $leaf;
                }

                continue;
            }

            $leaves[] = $node;
        }

        return $leaves;
    }

    /**
     * Return the fields the current request may see.
     *
     * Resolves the HTTP request from the container via `app('request')`. Outside
     * a real request context (console commands, queued jobs) the request object
     * is empty, so any user- or session-dependent gate will fail closed and hide
     * those fields. Design gates defensively with this in mind.
     *
     * @return array<int, Field>
     */
    public function visibleFields(): array
    {
        $request = app('request');

        return array_values(array_filter(
            $this->fields(),
            fn (Field $field) => $field->visibleTo($request),
        ));
    }

    public function model(Model $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function prototype(): ?Model
    {
        return $this->model;
    }

    protected function prepare(): void
    {
        if ($this->prepared || $this->model === null) {
            return;
        }

        foreach ($this->leaves($this->schema) as $field) {
            $field->bound($this->model);
        }

        $this->prepared = true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return collect($this->visibleFields())
            ->mapWithKeys(fn (Field $field) => [$field->name() => $field->getRules()])
            ->all();
    }

    /** @param array<string, mixed> $validated */
    public function fill(Model $record, array $validated): void
    {
        foreach ($this->visibleFields() as $field) {
            if (array_key_exists($field->name(), $validated)) {
                $field->fill($record, $validated[$field->name()]);
            }
        }
    }

    /**
     * Serialize the schema to a tree for the frontend. Leaf fields serialize
     * exactly as v0.6; layout containers serialize as `{layout, ..., schema}`
     * nodes with recursively-serialized children. Hidden (canSee-false) leaves
     * are excluded everywhere, so a flat schema produces the identical v0.6
     * payload. Containers are never bound or validated themselves.
     *
     * @return array<int, array<string, mixed>>
     */
    public function toInertia(?Model $record = null): array
    {
        $this->prepare();

        return $this->serializeNodes($this->schema, $record);
    }

    /**
     * @param  array<int, Field|Layout>  $nodes
     * @return array<int, array<string, mixed>>
     */
    protected function serializeNodes(array $nodes, ?Model $record): array
    {
        $request = app('request');
        $serialized = [];

        foreach ($nodes as $node) {
            if ($node instanceof Layout) {
                $serialized[] = $node->toInertia(
                    $record,
                    fn (array $children) => $this->serializeNodes($children, $record),
                );

                continue;
            }

            if (! $node->visibleTo($request)) {
                continue;
            }

            $serialized[] = $node->toArray($record);
        }

        return array_values($serialized);
    }
}
