<?php

declare(strict_types=1);

namespace Pedrosalpr\LaravelApiProblem;

use Pedrosalpr\LaravelApiProblem\Commands\LaravelApiProblemCommand;
use Pedrosalpr\LaravelApiProblem\Commands\LaravelApiProblemExceptionCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelApiProblemServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-api-problem')
            ->hasCommand(LaravelApiProblemCommand::class)
            ->hasCommand(LaravelApiProblemExceptionCommand::class);
    }
}
