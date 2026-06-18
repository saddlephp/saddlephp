<?php

declare(strict_types=1);

namespace SaddlePHP\Fields;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
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

    /** Column span inside a Grid. Null keeps the flat payload byte-identical to v0.6. */
    protected ?int $span = null;

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

    /** Set how many Grid columns this field spans. */
    public function columnSpan(int $span): static
    {
        $this->span = $span;

        return $this;
    }

    protected ?Closure $canSee = null;

    /**
     * Gate this field per request.
     *
     * Hidden fields are excluded from the form payload, validation, and fill —
     * they will not appear in `visibleFields()`, `rules()`, `fill()`, or `toInertia()`.
     *
     * The callback may be invoked several times per request (once per call to
     * `visibleFields()`), so keep it cheap and idempotent. Prefer pre-loaded
     * authorisation decisions over database queries inside the closure.
     *
     * Return a real boolean. For example, use `Gate::allows('view', $model)`
     * rather than `Gate::inspect(...)` — a `Response` object is always truthy
     * and will never hide the field.
     */
    public function canSee(Closure $callback): static
    {
        $this->canSee = $callback;

        return $this;
    }

    public function visibleTo(Request $request): bool
    {
        return $this->canSee === null || (bool) ($this->canSee)($request);
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
        ], $this->meta(), $this->span === null ? [] : ['span' => $this->span]);
    }

    /** @return array<string, mixed> */
    protected function meta(): array
    {
        return [];
    }

    /**
     * Serialize this field for the read-only view page: a label plus a
     * pre-formatted, render-safe display value and a type hint the frontend uses
     * to pick a type-aware leaf. The 'display-entry' component is a leaf marker
     * (so the shared form-tree walkers find it) — it is never an input. Subclasses
     * override displayType()/displayValue() to format relations, booleans, dates,
     * files, etc.
     *
     * @return array<string, mixed>
     */
    public function toDisplay(?Model $record = null): array
    {
        return array_merge([
            'component' => 'display-entry',
            'name' => $this->name,
            'label' => $this->label ?? Str::headline($this->name),
            'type' => $this->displayType(),
            'display' => $this->displayValue($record),
        ], $this->span === null ? [] : ['span' => $this->span]);
    }

    /** The display leaf type the frontend renders. Subclasses may override. */
    protected function displayType(): string
    {
        return 'text';
    }

    /** The formatted, render-safe value shown on the view page. */
    protected function displayValue(?Model $record): mixed
    {
        if ($record === null) {
            return null;
        }

        $value = $this->resolve($record);

        return $value === null ? null : (string) $value;
    }

    /**
     * Hook invoked with the owning resource's model prototype before the
     * form is consumed. Lets relation-aware fields derive their metadata.
     */
    public function bound(Model $prototype): void
    {
        //
    }
}
