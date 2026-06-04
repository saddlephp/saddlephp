<?php

declare(strict_types=1);

namespace SaddlePHP;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use SaddlePHP\Forms\Form;
use SaddlePHP\Tables\Table;

abstract class Resource
{
    /** @var class-string<Model> */
    public static string $model;

    /** Attribute used as the record title; defaults to the model key. */
    public static ?string $title = null;

    public static ?string $icon = null;

    public static ?string $group = null;

    /** @var array<int, string> Relations eager-loaded by the base query. */
    public static array $with = [];

    abstract public static function form(Form $form): Form;

    abstract public static function table(Table $table): Table;

    public static function label(): string
    {
        return Str::headline(Str::plural(static::baseName()));
    }

    public static function singularLabel(): string
    {
        return Str::headline(static::baseName());
    }

    public static function uriKey(): string
    {
        return Str::kebab(Str::plural(static::baseName()));
    }

    public static function newModel(): Model
    {
        return new static::$model;
    }

    public static function query(Request $request): Builder
    {
        return static::$model::query()->with(static::$with);
    }

    public static function recordTitle(Model $record): string
    {
        return (string) data_get($record, static::$title ?? $record->getKeyName());
    }

    public static function makeForm(): Form
    {
        return static::form(Form::make()->model(static::newModel()));
    }

    public static function makeTable(): Table
    {
        return static::table(Table::make());
    }

    public static function allows(string $ability, Model|string|null $target = null): bool
    {
        if (Gate::getPolicyFor(static::$model) === null) {
            return true;
        }

        return (bool) Auth::user()?->can($ability, $target ?? static::$model);
    }

    protected static function baseName(): string
    {
        return Str::beforeLast(class_basename(static::class), 'Resource');
    }
}
