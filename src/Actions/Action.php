<?php

declare(strict_types=1);

namespace SaddlePHP\Actions;

use Closure;
use Illuminate\Support\Str;

/**
 * A single-record row action shown on the index table.
 *
 * The handle Closure receives the resolved Eloquent Model instance for the row.
 * When authorize() is unset the action is available to anyone who can see the
 * index. For destructive or sensitive actions declare an ability so the policy
 * is checked per record before the handler runs.
 *
 * Color tokens: accent | ink | muted
 */
class Action
{
    protected ?string $label = null;

    protected string $color = 'ink';

    protected ?string $confirm = null;

    protected ?string $ability = null;

    protected ?Closure $callback = null;

    protected string $successMessage = 'Done.';

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

    /**
     * Set the display color token. Accepted tokens: accent | ink | muted.
     * Any string is accepted; unknown tokens fall back to the default panel styling.
     */
    public function color(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Require the user to confirm before the action runs.
     * When $message is null the default "Are you sure?" prompt is shown.
     */
    public function requiresConfirmation(?string $message = null): static
    {
        $this->confirm = $message ?? 'Are you sure?';

        return $this;
    }

    /**
     * Name the policy ability checked per record before the handler runs.
     * The check is performed via Resource::allows($ability, $record).
     */
    public function authorize(string $ability): static
    {
        $this->ability = $ability;

        return $this;
    }

    /**
     * Supply the handler Closure for this action.
     * For row actions the Closure receives the resolved Eloquent Model.
     * For BulkActions the Closure receives an Eloquent Collection.
     */
    public function handle(Closure $callback): static
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Set the flash message shown on successful execution.
     * Defaults to "Done.".
     */
    public function successMessage(string $message): static
    {
        $this->successMessage = $message;

        return $this;
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function name(): string
    {
        return $this->name;
    }

    public function ability(): ?string
    {
        return $this->ability;
    }

    public function callback(): ?Closure
    {
        return $this->callback;
    }

    public function message(): string
    {
        return $this->successMessage;
    }

    // -------------------------------------------------------------------------
    // Serialization
    // -------------------------------------------------------------------------

    /** @return array{name: string, label: string, color: string, confirm: string|null} */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label ?? Str::headline($this->name),
            'color' => $this->color,
            'confirm' => $this->confirm,
        ];
    }
}
