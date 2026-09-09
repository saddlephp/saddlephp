<?php

declare(strict_types=1);

namespace SaddlePHP\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use SaddlePHP\Fields\Text;
use SaddlePHP\Forms\Form;
use SaddlePHP\Resource;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Table;
use Workbench\App\Models\Horse;

/**
 * A resource whose age column is both formatted and sortable -- the pairing a
 * model accessor cannot give you, because sorting happens in the database and
 * an accessor does not exist there.
 */
class FormattedHorseResource extends Resource
{
    public static string $model = Horse::class;

    public static ?string $title = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([Text::make('name')]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name'),
            TextColumn::make('age')
                ->sortable()
                ->formatUsing(fn (mixed $value, Model $record) => $value === null
                    ? 'unrecorded'
                    : "{$record->name} is {$value}"),
        ]);
    }
}
