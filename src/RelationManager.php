<?php

declare(strict_types=1);

namespace SaddlePHP;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use LogicException;
use SaddlePHP\Forms\Form;
use SaddlePHP\Tables\Table;

/**
 * A HasMany relation manager: a Resource-shaped definition (table + form) bound
 * to a parent record's relationship. Every read and write flows through
 * relationFor($parent), so related records are inherently scoped to the parent
 * and cross-parent (and so cross-tenant) access is impossible.
 */
abstract class RelationManager
{
    /** The HasMany relationship method on the PARENT model. */
    protected static string $relationship;

    /** Related-record title attribute; defaults to the related key. */
    public static ?string $title = null;

    abstract public static function table(Table $table): Table;

    abstract public static function form(Form $form): Form;

    /** The HasMany relation for a parent, validated. */
    public static function relationFor(Model $parent): HasMany
    {
        if (! method_exists($parent, static::$relationship)) {
            throw new LogicException(sprintf(
                'RelationManager [%s]: %s has no %s() relation method.',
                static::class, $parent::class, static::$relationship,
            ));
        }

        $relation = $parent->{static::$relationship}();

        if (! $relation instanceof HasMany) {
            throw new LogicException(sprintf(
                'RelationManager [%s]: %s::%s() is not a HasMany relation.',
                static::class, $parent::class, static::$relationship,
            ));
        }

        return $relation;
    }

    /** @return class-string<Model> */
    public static function relatedModel(Model $parent): string
    {
        return static::relationFor($parent)->getRelated()::class;
    }

    /** A new related instance with the parent foreign key already set. */
    public static function newRelatedFor(Model $parent): Model
    {
        return static::relationFor($parent)->make();
    }

    public static function uriKey(): string
    {
        return Str::kebab(static::$relationship);
    }

    public static function label(): string
    {
        return Str::headline(static::$relationship);
    }

    public static function singularLabel(): string
    {
        return Str::headline(Str::singular(static::$relationship));
    }

    public static function recordTitle(Model $record): string
    {
        return (string) data_get($record, static::$title ?? $record->getKeyName());
    }

    public static function makeTable(): Table
    {
        return static::table(Table::make());
    }

    /** A form bound to a fresh related prototype (so relation fields resolve). */
    public static function makeForm(Model $parent): Form
    {
        return static::form(Form::make()->model(static::newRelatedFor($parent)));
    }

    /**
     * Authorize an ability against the RELATED model's policy, mirroring
     * Resource::allows (fail-open by default; the require_policy flag flips to
     * fail-closed when no policy is registered).
     */
    public static function allows(Model $parent, string $ability, Model|string|null $target = null): bool
    {
        $relatedClass = static::relatedModel($parent);

        if (Gate::getPolicyFor($relatedClass) === null) {
            return ! config('saddle.authorization.require_policy', false);
        }

        return (bool) Auth::user()?->can($ability, $target ?? $relatedClass);
    }
}
