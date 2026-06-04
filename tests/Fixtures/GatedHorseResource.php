<?php

declare(strict_types=1);

namespace SaddlePHP\Tests\Fixtures;

use SaddlePHP\Fields\BelongsTo;
use SaddlePHP\Fields\Text;
use SaddlePHP\Forms\Form;
use SaddlePHP\Resource;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Table;
use Workbench\App\Models\Horse;

class GatedHorseResource extends Resource
{
    public static string $model = Horse::class;

    public static ?string $title = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Text::make('name'),
            BelongsTo::make('rider')->canSee(fn () => false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')]);
    }
}
