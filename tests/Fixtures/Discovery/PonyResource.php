<?php

declare(strict_types=1);

namespace SaddlePHP\Tests\Fixtures\Discovery;

use SaddlePHP\Forms\Form;
use SaddlePHP\Resource;
use SaddlePHP\Tables\Table;
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
