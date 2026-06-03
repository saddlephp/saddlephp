<?php

declare(strict_types=1);

namespace RodeoPHP\Forms;

use Illuminate\Database\Eloquent\Model;
use RodeoPHP\Fields\Field;

class Form
{
    /** @var array<int, Field> */
    protected array $fields = [];

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
        return $this->fields;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return collect($this->fields)
            ->mapWithKeys(fn (Field $field) => [$field->name() => $field->getRules()])
            ->all();
    }

    /** @param array<string, mixed> $validated */
    public function fill(Model $record, array $validated): void
    {
        foreach ($this->fields as $field) {
            if (array_key_exists($field->name(), $validated)) {
                $field->fill($record, $validated[$field->name()]);
            }
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function toInertia(?Model $record = null): array
    {
        return collect($this->fields)
            ->map(fn (Field $field) => $field->toArray($record))
            ->values()->all();
    }
}
