<?php

declare(strict_types=1);

namespace SaddlePHP\Fields;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LogicException;
use SaddlePHP\Resource;
use SaddlePHP\Saddle;
use SaddlePHP\Support\Search;

class BelongsTo extends Field
{
    protected string $component = 'select-field';

    protected string $relationName;

    protected ?string $titleAttribute = null;

    protected int $limit = 100;

    protected bool $searchable = false;

    protected ?Closure $modifyOptionsQuery = null;

    /** @var class-string<Model>|null */
    protected ?string $relatedModel = null;

    protected ?string $relatedKeyName = null;

    public static function make(string $name): static
    {
        $field = parent::make($name);
        $field->relationName = $name;
        $field->label(Str::headline($name));

        return $field;
    }

    public function titleAttribute(string $attribute): static
    {
        $this->titleAttribute = $attribute;

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Hook to scope the related options query (tenancy, visibility). Runs after
     * the base ordering and limit, so added orderBy calls become secondary sorts.
     */
    public function modifyOptionsQuery(Closure $callback): static
    {
        $this->modifyOptionsQuery = $callback;

        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        $this->component = $searchable ? 'search-select-field' : 'select-field';

        return $this;
    }

    public function toArray(?Model $record = null): array
    {
        $payload = parent::toArray($record);

        if ($this->searchable) {
            $payload['async'] = true;
            $payload['options'] = $record !== null ? $this->currentOption($record) : [];
        }

        return $payload;
    }

    /** @return array<int, array{value: mixed, label: string}> */
    protected function currentOption(Model $record): array
    {
        $key = data_get($record, $this->name);

        if ($key === null || $this->relatedModel === null) {
            return [];
        }

        // Deliberately bypasses modifyOptionsQuery: a persisted FK must keep
        // rendering its label even when the row falls outside the hook's scope.
        $related = $this->relatedModel::query()->whereKey($key)->first();

        return $related === null ? [] : $this->mapOptions(new Collection([$related]), $this->resolveTitleAttribute());
    }

    public function bound(Model $prototype): void
    {
        if (! method_exists($prototype, $this->relationName)) {
            throw new LogicException(sprintf(
                'BelongsTo field [%s]: %s has no %s() relation method.',
                $this->relationName, $prototype::class, $this->relationName,
            ));
        }

        $relation = $prototype->{$this->relationName}();

        if (! $relation instanceof BelongsToRelation) {
            throw new LogicException(sprintf(
                'BelongsTo field [%s]: %s::%s() is not a BelongsTo relation.',
                $this->relationName, $prototype::class, $this->relationName,
            ));
        }

        $this->name = $relation->getForeignKeyName();
        $this->relatedModel = $relation->getRelated()::class;
        $this->relatedKeyName = $relation->getRelated()->getKeyName();
    }

    protected function typeRules(): array
    {
        if ($this->relatedModel === null) {
            return [];
        }

        $rule = Rule::exists((new $this->relatedModel)->getTable(), $this->relatedKeyName);

        // Mirror the options query: when tenancy is active and the related
        // resource is tenant-scoped, the existence check must be confined to the
        // current tenant. Otherwise a tenant-A write could reference a tenant-B
        // row whose key happens to exist. Stays unscoped when tenancy is off or
        // the related resource is global (lookup-table) by design.
        $constraint = $this->tenantConstraint();

        if ($constraint !== null) {
            [$foreignKey, $tenantKey] = $constraint;
            $rule->where($foreignKey, $tenantKey);
        }

        return [$rule];
    }

    protected function meta(): array
    {
        return $this->searchable ? [] : ['options' => $this->options()];
    }

    protected function displayValue(?Model $record): mixed
    {
        if ($record === null) {
            return null;
        }

        // currentOption resolves the persisted FK to its title label, exactly as
        // the searchable picker renders the bound value.
        return $this->currentOption($record)[0]['label'] ?? null;
    }

    /** @return array<int, array{value: mixed, label: string}> */
    public function searchOptions(string $search = ''): array
    {
        if ($this->relatedModel === null) {
            return [];
        }

        $title = $this->resolveTitleAttribute();
        $query = $this->optionsQuery($title);

        if ($search !== '') {
            $title !== null
                ? $query->where($title, 'like', '%'.Search::escapeLike($search).'%')
                : $query->whereKey($search);
        }

        return $this->mapOptions($query->get(), $title);
    }

    /** @return array<int, array{value: mixed, label: string}> */
    protected function options(): array
    {
        if ($this->relatedModel === null) {
            return [];
        }

        $title = $this->resolveTitleAttribute();

        return $this->mapOptions($this->optionsQuery($title)->get(), $title);
    }

    protected function optionsQuery(?string $title): Builder
    {
        $query = $this->relatedModel::query()
            ->orderBy($title ?? $this->relatedKeyName)
            ->limit($this->limit);

        $this->scopeToTenant($query);

        if ($this->modifyOptionsQuery !== null) {
            $query = ($this->modifyOptionsQuery)($query) ?? $query;
        }

        return $query;
    }

    /**
     * Scope the options to the bound tenant when the related model's registered
     * resource is tenant-scoped. Runs before modifyOptionsQuery so the developer
     * hook composes on top of an already-secured query.
     */
    protected function scopeToTenant(Builder $query): void
    {
        $tenant = app(Saddle::class)->tenant();

        if ($tenant === null) {
            return;
        }

        $resource = $this->relatedResource();

        if ($resource !== null && $resource::$tenant !== null) {
            $query->whereBelongsTo($tenant, $resource::$tenant);
        }
    }

    /**
     * Derive the [foreignKey, tenantKey] pair that confines the related model to
     * the bound tenant, or null when scoping does not apply (tenancy off, no
     * bound tenant, related model unbound, or the related resource is global).
     *
     * The foreign key is read straight off the related model's tenant BelongsTo
     * relation, exactly as whereBelongsTo() would resolve it for the options
     * query — keeping the existence check and the options list in lockstep.
     *
     * @return array{0: string, 1: mixed}|null
     */
    protected function tenantConstraint(): ?array
    {
        if ($this->relatedModel === null) {
            return null;
        }

        $tenant = app(Saddle::class)->tenant();

        if ($tenant === null) {
            return null;
        }

        $resource = $this->relatedResource();

        if ($resource === null || $resource::$tenant === null) {
            return null;
        }

        $relation = (new $this->relatedModel)->{$resource::$tenant}();

        if (! $relation instanceof BelongsToRelation) {
            return null;
        }

        return [$relation->getForeignKeyName(), $tenant->getKey()];
    }

    /** @return array<int, array{value: mixed, label: string}> */
    protected function mapOptions(Collection $records, ?string $title): array
    {
        return $records
            ->map(fn (Model $record) => [
                'value' => $record->getKey(),
                'label' => $title !== null
                    ? (string) data_get($record, $title)
                    : (string) $record->getKey(),
            ])
            ->values()->all();
    }

    protected function resolveTitleAttribute(): ?string
    {
        if ($this->titleAttribute !== null) {
            return $this->titleAttribute;
        }

        $resource = $this->relatedResource();

        return $resource !== null ? $resource::$title : null;
    }

    /** @return class-string<resource>|null */
    protected function relatedResource(): ?string
    {
        return app(Saddle::class)->resources()
            ->first(fn (string $resource) => $resource::$model === $this->relatedModel);
    }
}
