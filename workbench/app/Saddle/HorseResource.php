<?php

declare(strict_types=1);

namespace Workbench\App\Saddle;

use SaddlePHP\Fields\Select;
use SaddlePHP\Fields\Text;
use SaddlePHP\Fields\Textarea;
use SaddlePHP\Fields\Toggle;
use SaddlePHP\Forms\Form;
use SaddlePHP\Resource;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Table;
use Workbench\App\Models\Horse;

class HorseResource extends Resource
{
    public static string $model = Horse::class;

    public static ?string $title = 'name';

    public static ?string $icon = 'collection';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Text::make('name')->required()->rules('max:120'),
            Select::make('breed')->options([
                'quarter' => 'Quarter Horse',
                'mustang' => 'Mustang',
                'appaloosa' => 'Appaloosa',
            ]),
            Textarea::make('notes')->rows(3),
            Toggle::make('is_saddled'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->sortable()->searchable(),
            TextColumn::make('breed')->sortable(),
            TextColumn::make('created_at')->sortable(),
        ]);
    }
}
