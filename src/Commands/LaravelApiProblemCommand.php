<?php

declare(strict_types=1);

namespace Pedrosalpr\LaravelApiProblem\Commands;

use Illuminate\Console\GeneratorCommand;

class LaravelApiProblemCommand extends GeneratorCommand
{
    public $signature = 'laravel-api-problem:extend {name}';

    public $description = 'Extend class api problem';

    protected $type = 'LaravelApiProblem';

    protected function getStub(): string
    {
        return $this->resolveStubPath('/Stubs/dummy.stub');
    }

    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return "{$rootNamespace}\\ApiProblem";
    }
}
