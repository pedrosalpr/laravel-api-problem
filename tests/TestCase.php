<?php

declare(strict_types=1);

namespace Pedrosalpr\LaravelApiProblem\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Pedrosalpr\LaravelApiProblem\LaravelApiProblemServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelApiProblemServiceProvider::class,
        ];
    }
}
