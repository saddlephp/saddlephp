<?php

declare(strict_types=1);

namespace SaddlePHP\Tests\Fixtures;

use SaddlePHP\Fields\Password;
use SaddlePHP\Fields\Text;
use SaddlePHP\Forms\Form;
use SaddlePHP\Resource;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Table;

/** A user-administration screen: the shape item 4 came out of. */
class PanelUserResource extends Resource
{
    public static string $model = HashedUser::class;

    public static ?string $title = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Text::make('name')->required(),
            Text::make('email')->type('email')->required(),
            Password::make('password')->helper('Leave blank to keep the current password.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name'), TextColumn::make('email')]);
    }
}
