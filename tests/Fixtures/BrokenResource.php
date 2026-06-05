<?php

declare(strict_types=1);

namespace SaddlePHP\Tests\Fixtures;

use RuntimeException;
use SaddlePHP\Forms\Form;
use SaddlePHP\Resource;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Table;
use Workbench\App\Models\Horse;

/**
 * A deliberately broken resource: building its nav item throws. Used to prove
 * one bad resource can't take down the whole sidebar.
 */
class BrokenResource extends Resource
{
    public static string $model = Horse::class;

    public static function label(): string
    {
        throw new RuntimeException('This resource is broken.');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')]);
    }
}
