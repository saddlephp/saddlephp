<?php

declare(strict_types=1);

namespace SaddlePHP\Tests\Fixtures;

use SaddlePHP\Fields\CustomField;
use SaddlePHP\Fields\Text;
use SaddlePHP\Forms\Form;
use SaddlePHP\Resource;
use SaddlePHP\Tables\Columns\CustomColumn;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Table;
use Workbench\App\Models\Horse;

class PluginHorseResource extends Resource
{
    public static string $model = Horse::class;

    public static ?string $title = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Text::make('name')->required(),
            CustomField::make('breed')->tag('breed-picker'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name'),
            CustomColumn::make('breed')->tag('breed-cell'),
        ]);
    }
}
