<?php

declare(strict_types=1);

namespace RodeoPHP\Fields;

use Illuminate\Database\Eloquent\Model;

class Toggle extends Field
{
    protected string $component = 'toggle-field';

    protected mixed $default = false;

    /**
     * Toggles cannot be required — an absent or unchecked toggle is valid by design,
     * so this is a deliberate no-op to keep serialization and validation consistent.
     */
    public function required(bool $required = true): static
    {
        return $this;
    }

    /** Toggles are always nullable booleans; absent/false must validate. */
    public function getRules(): array
    {
        return array_merge(['nullable', 'boolean'], $this->rules);
    }

    public function fill(Model $record, mixed $value): void
    {
        $record->{$this->name} = (bool) $value;
    }
}
