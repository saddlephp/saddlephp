<?php

declare(strict_types=1);

namespace SaddlePHP\Tables\Columns;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LogicException;

abstract class Column
{
    protected ?string $label = null;

    protected bool $sortable = false;

    protected bool $searchable = false;

    protected string $type = 'text';

    protected ?Closure $canSee = null;

    /** @var (Closure(mixed, Model): mixed)|null */
    protected ?Closure $formatUsing = null;

    final public function __construct(protected string $name) {}

    /**
     * Transform a cell's value between resolving it and rendering it.
     *
     * A cell used to render exactly `data_get($record, $name)`, with no hook in
     * between, so an application could not print a null as anything but an
     * empty cell -- and could not reach for a model accessor instead, because
     * an accessor cannot be `->sortable()`: sorting happens in the database and
     * the accessor does not exist there. On a panel where "a null is not a
     * zero" is a stated rule, the same figure was an em dash in a StatWidget
     * (whose value() returns a string) and an empty gap in the table below it.
     *
     *     TextColumn::make('spend')
     *         ->sortable()
     *         ->formatUsing(fn (mixed $value) => $value === null ? '—' : Number::currency($value));
     *
     * The callback receives the value AND the record, so it can format from a
     * related field without a second query. It runs AFTER the value is
     * resolved, which is the whole reason it belongs here: `sortable()` and
     * `searchable()` still refer to the real database column.
     *
     * It applies wherever a cell's value is resolved, the CSV export included
     * -- an export of a formatted column exports the formatted text.
     *
     * On a `TextColumn` that also declares `date()`, the callback sees the raw
     * value (a `DateTimeInterface`, typically) and `date()` then formats only
     * what is still a date afterwards. Format the date yourself in the callback
     * if you want both.
     *
     * @param  Closure(mixed, Model): mixed  $callback
     */
    public function formatUsing(Closure $callback): static
    {
        $this->formatUsing = $callback;

        return $this;
    }

    /**
     * Gate this column per request, mirroring `Field::canSee()`.
     *
     * A hidden column is dropped from the index cells, the columns payload, the
     * CSV export, and the sortable and searchable lists. Dropping it from the
     * last two matters as much as the cells: a column that is merely un-rendered
     * is still an oracle, because `?sort=` leaks ordering and `?search=` leaks
     * which rows match a guess.
     *
     * The callback may be invoked several times per request, so keep it cheap
     * and idempotent. Prefer pre-loaded authorisation decisions over database
     * queries inside the closure.
     *
     * Return a real boolean. For example, use `Gate::allows('view', $model)`
     * rather than `Gate::inspect(...)` -- a `Response` object is always truthy
     * and would never hide the column.
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

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function sortable(bool $sortable = true): static
    {
        if ($sortable) {
            $this->assertNotRelationColumn('sorted');
        }

        $this->sortable = $sortable;

        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        if ($searchable) {
            $this->assertNotRelationColumn('searched');
        }

        $this->searchable = $searchable;

        return $this;
    }

    /**
     * Relation (dot-path) columns read through a loaded relation in PHP and
     * have no real database column to target, so sorting or searching them
     * would later compile to invalid SQL and 500 the index. Fail loudly at
     * build time instead.
     */
    protected function assertNotRelationColumn(string $verb): void
    {
        if (str_contains($this->name, '.')) {
            throw new LogicException(
                "Column [{$this->name}] is a relation column and cannot be {$verb}. ".
                'Relation (dot-path) columns are not sortable or searchable yet.'
            );
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function resolve(Model $record): mixed
    {
        return $this->format(data_get($record, $this->name), $record);
    }

    /**
     * Apply the formatting callback, if one is registered.
     *
     * Subclasses that resolve a value of their own route it through here, so
     * `formatUsing()` means the same thing on every column type rather than
     * silently doing nothing on the ones that override resolve().
     */
    protected function format(mixed $value, Model $record): mixed
    {
        return $this->formatUsing === null ? $value : ($this->formatUsing)($value, $record);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_merge([
            'name' => $this->name,
            'label' => $this->label ?? Str::headline($this->name),
            'sortable' => $this->sortable,
            'type' => $this->type,
        ], $this->meta());
    }

    /** @return array<string, mixed> */
    protected function meta(): array
    {
        return [];
    }
}
