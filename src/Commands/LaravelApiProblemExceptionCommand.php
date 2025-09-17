<?php

declare(strict_types=1);

namespace Pedrosalpr\LaravelApiProblem\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

class LaravelApiProblemExceptionCommand extends GeneratorCommand
{
    public $signature = 'laravel-api-problem:exception {name}';

    public $description = 'Make class api problem exception';

    protected $type = 'LaravelApiProblemException';

    protected function getStub(): string
    {
        return $this->resolveStubPath('/Stubs/exception.stub');
    }

    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return "{$rootNamespace}\\Exceptions\\ApiProblem";
    }

    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        // Do string replacement
        return str_replace('{{ class }}', $class, $stub);
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name and root of the file.'],
        ];
    }
}
