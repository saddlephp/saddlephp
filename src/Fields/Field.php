<?php

declare(strict_types=1);

namespace RodeoPHP\Fields;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class Field
{
    /** The frontend component that renders this field. Subclasses MUST set this. */
    protected string $component;

    protected ?string $label = null;

    protected bool $required = false;

    /** @var array<int, string|\Stringable|ValidationRule|object> Custom validation rules appended after type rules. */
    protected array $rules = [];

    protected mixed $default = null;

    protected ?string $placeholder = null;

    protected ?string $helper = null;

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

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    public function rules(string|array ...$rules): static
    {
        $this->rules = array_merge($this->rules, collect($rules)->flatten()->all());

        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;

        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function helper(string $helper): static
    {
        $this->helper = $helper;

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    /** @return array<int, mixed> */
    public function getRules(): array
    {
        return array_merge(
            [$this->required ? 'required' : 'nullable'],
            $this->typeRules(),
            $this->rules,
        );
    }

    /** @return array<int, mixed> */
    protected function typeRules(): array
    {
        return [];
    }

    public function resolve(Model $record): mixed
    {
        return data_get($record, $this->name);
    }

    public function fill(Model $record, mixed $value): void
    {
        $record->{$this->name} = $value;
    }

    /** @return array<string, mixed> */
    public function toArray(?Model $record = null): array
    {
        return array_merge([
            'component' => $this->component,
            'name' => $this->name,
            'label' => $this->label ?? Str::headline($this->name),
            'required' => $this->required,
            'placeholder' => $this->placeholder,
            'helper' => $this->helper,
            'value' => $record ? $this->resolve($record) : $this->default,
        ], $this->meta());
    }

    /** @return array<string, mixed> */
    protected function meta(): array
    {
        return [];
    }
}
