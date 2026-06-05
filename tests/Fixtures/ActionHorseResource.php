<?php

declare(strict_types=1);

namespace SaddlePHP\Tests\Fixtures;

use Illuminate\Database\Eloquent\Collection;
use SaddlePHP\Actions\Action;
use SaddlePHP\Actions\BulkAction;
use SaddlePHP\Fields\Text;
use SaddlePHP\Forms\Form;
use SaddlePHP\Resource;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Table;
use Workbench\App\Models\Horse;

class ActionHorseResource extends Resource
{
    public static string $model = Horse::class;

    public static ?string $title = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Text::make('name')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name'),
        ]);
    }

    /** @return array<int, Action> */
    public static function actions(): array
    {
        return [
            Action::make('rename')
                ->handle(fn (Horse $horse) => $horse->update(['name' => 'Renamed']))
                ->successMessage('Renamed.'),
            Action::make('guarded')
                ->authorize('update')
                ->handle(fn (Horse $horse) => $horse->update(['name' => 'Guarded'])),
            // Declared without handle() on purpose: hitting it is a developer
            // error and must surface as a LogicException, not a silent no-op.
            Action::make('hollow'),
        ];
    }

    /** @return array<int, Action> */
    public static function bulkActions(): array
    {
        return [
            BulkAction::make('brand')
                ->handle(fn (Collection $horses) => $horses->each->update(['breed' => 'branded'])),
            BulkAction::delete(),
        ];
    }
}
