<?php

declare(strict_types=1);

namespace Workbench\App\Saddle;

use SaddlePHP\Fields\Text;
use SaddlePHP\Forms\Form;
use SaddlePHP\Resource;
use SaddlePHP\Tables\Columns\TextColumn;
use SaddlePHP\Tables\Table;
use Workbench\App\Models\Ranch;
use Workbench\App\Saddle\RelationManagers\HorsesRelationManager;

class RanchResource extends Resource
{
    public static string $model = Ranch::class;

    public static ?string $title = 'name';

    // Own storage so the tenancy suite's runtime $tenant toggling stays local.
    public static ?string $tenant = null;

    public static function relations(): array
    {
        return [HorsesRelationManager::class];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Text::make('name')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->sortable()->searchable(),
        ]);
    }
}
