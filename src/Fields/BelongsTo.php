<?php

declare(strict_types=1);

namespace SaddlePHP\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LogicException;
use SaddlePHP\Resource;
use SaddlePHP\Saddle;

class BelongsTo extends Field
{
    protected string $component = 'select-field';

    protected string $relationName;

    protected ?string $titleAttribute = null;

    protected int $limit = 100;

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

        return [Rule::exists((new $this->relatedModel)->getTable(), $this->relatedKeyName)];
    }

    protected function meta(): array
    {
        return ['options' => $this->options()];
    }

    /** @return array<int, array{value: mixed, label: string}> */
    protected function options(): array
    {
        if ($this->relatedModel === null) {
            return [];
        }

        $title = $this->resolveTitleAttribute();
        $orderBy = $title ?? $this->relatedKeyName;

        return $this->relatedModel::query()
            ->orderBy($orderBy)
            ->limit($this->limit)
            ->get()
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
