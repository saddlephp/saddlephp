<?php

declare(strict_types=1);

namespace SaddlePHP\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ResourceRelationMakeCommand extends GeneratorCommand
{
    protected $name = 'saddle:relation';

    protected $description = 'Create a new Saddle relation manager class';

    protected $type = 'RelationManager';

    protected function getStub(): string
    {
        return __DIR__.'/stubs/relation.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Saddle\\RelationManagers';
    }

    protected function buildClass($name): string
    {
        $relationship = $this->option('relationship')
            ?: Str::camel(Str::plural(Str::beforeLast(class_basename($this->getNameInput()), 'RelationManager')));

        return str_replace('{{ relationship }}', $relationship, parent::buildClass($name));
    }

    protected function getOptions(): array
    {
        return [
            ['relationship', null, InputOption::VALUE_OPTIONAL, 'The parent HasMany relationship method this manager edits'],
        ];
    }
}
