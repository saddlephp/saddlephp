<?php

declare(strict_types=1);

namespace SaddlePHP\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ResourceMakeCommand extends GeneratorCommand
{
    protected $name = 'saddle:resource';

    protected $description = 'Create a new Saddle resource class';

    protected $type = 'Resource';

    protected function getStub(): string
    {
        return __DIR__.'/stubs/resource.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Saddle';
    }

    protected function buildClass($name): string
    {
        $model = $this->option('model') ?: $this->guessModel();

        if (! str_contains($model, '\\')) {
            $model = $this->rootNamespace().'Models\\'.$model;
        }

        return str_replace('{{ model }}', '\\'.ltrim($model, '\\'), parent::buildClass($name));
    }

    protected function guessModel(): string
    {
        return Str::beforeLast(class_basename($this->getNameInput()), 'Resource');
    }

    protected function getOptions(): array
    {
        return [
            ['model', null, InputOption::VALUE_OPTIONAL, 'The Eloquent model the resource manages'],
        ];
    }
}
