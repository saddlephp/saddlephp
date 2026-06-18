<?php

declare(strict_types=1);

namespace Workbench\App\Saddle\RelationManagers;

use SaddlePHP\Fields\Text;
use SaddlePHP\Fields\Toggle;
use SaddlePHP\Forms\Form;
use SaddlePHP\RelationManager;
use SaddlePHP\Tables\Columns\BooleanColumn;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Table;

class HorsesRelationManager extends RelationManager
{
    protected static string $relationship = 'horses';

    public static ?string $title = 'name';

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name'),
            BooleanColumn::make('is_saddled'),
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Text::make('name')->required()->rules('max:120'),
            Toggle::make('is_saddled'),
        ]);
    }
}
