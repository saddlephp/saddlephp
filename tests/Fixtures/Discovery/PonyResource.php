<?php

declare(strict_types=1);

namespace RodeoPHP\Tests\Fixtures\Discovery;

use RodeoPHP\Forms\Form;
use RodeoPHP\Resource;
use RodeoPHP\Tables\Table;
use Workbench\App\Models\Horse;

class PonyResource extends Resource
{
    public static string $model = Horse::class;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }
}
