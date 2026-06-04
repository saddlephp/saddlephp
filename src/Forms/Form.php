<?php

declare(strict_types=1);

namespace SaddlePHP\Forms;

use Illuminate\Database\Eloquent\Model;
use SaddlePHP\Fields\Field;

class Form
{
    /** @var array<int, Field> */
    protected array $fields = [];

    protected ?Model $model = null;

    protected bool $prepared = false;

    public static function make(): self
    {
        return new self;
    }

    /** @param array<int, Field> $fields */
    public function schema(array $fields): static
    {
        $this->fields = $fields;

        return $this;
    }

    /** @return array<int, Field> */
    public function fields(): array
    {
        $this->prepare();

        return $this->fields;
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

        foreach ($this->fields as $field) {
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

    /** @return array<int, array<string, mixed>> */
    public function toInertia(?Model $record = null): array
    {
        return collect($this->visibleFields())
            ->map(fn (Field $field) => $field->toArray($record))
            ->values()->all();
    }
}
