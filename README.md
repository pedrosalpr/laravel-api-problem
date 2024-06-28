# Laravel Api Problem

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pedrosalpr/laravel-api-problem.svg?style=flat-square)](https://packagist.org/packages/pedrosalpr/laravel-api-problem)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/pedrosalpr/laravel-api-problem/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/pedrosalpr/laravel-api-problem/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/pedrosalpr/laravel-api-problem/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/pedrosalpr/laravel-api-problem/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/pedrosalpr/laravel-api-problem.svg?style=flat-square)](https://packagist.org/packages/pedrosalpr/laravel-api-problem)

The objective of this package is to facilitate error outputs from API requests in accordance with the [RFC 9457](https://datatracker.ietf.org/doc/rfc9457/) standard.

It transforms error outputs into json format with the following characteristics:
- header:
    - `Content-Type: application/problem+json`
- response:
    - `type`: URI that identifies the type of error that occured, for example `https://example.com/validation-error`.
    - `title`: Human-readable identifier, usually the same type field should have the same title field alongside. An example would be something like Form validation failed.
    - `status`: A copy of the HTTP status code.
    - `detail`: More information about the specific problem, and if it's appropriate also steps to correct it. For example information about a form validation problem Username is already taken, please choose a different username.
    - `instance`: An identifier for this specific occurence, which may not be useful to the client but may be included in a bug report for example.
    - **Additional fields**: Any fields may be added to give additional information, and consuming clients are expected to ignore any that they don't have specific support for.
        - `timestamp`: (RFC 9457 enhancement): A timestamp indicating when the problem was generated. This helps in logging and tracing the error, especially in systems where timing is critical.

Example:

Server Response:
```bash
HTTP/1.1 422 Unprocessable Entity
Content-Type: application/problem+json
Content-Language: en
{
     "status": 422,
     "type": "https://example.test/validation-error",
     "title": "Form validation failed",
     "detail": "One or more fields have validation errors. Please check and try again.",
     "instance": "http://example.test/api/test/1",
     "timestamp": "2024-02-09T11:55:51.252968Z",
     "errors": [
        {
            "name": "username",
            "reason": "Username is already taken."
        },
        {
            "name": "email",
            "reason": "Email format is invalid."
        }
    ]
}
```

## Installation

You can install the package via composer:

```bash
composer require pedrosalpr/laravel-api-problem
```

## Usage

### Default Mode (Older Version Laravel 9 and 10)

To use it, just go to the `register` method within `Exceptions\Handler.php` and add the following code:

```php
use Pedrosalpr\LaravelApiProblem\LaravelApiProblem;

public function register(): void
{
    ...

    $this->renderable(function (\Throwable $e, Request $request) {
        if ($request->is('api/*') || $this->shouldReturnJson($request, $e)) {
            $apiProblem = new LaravelApiProblem($e, $request);
            return $apiProblem->render();
        }
    });
}
```

If you want to debug, just add the following line before the return:

`dd($apiProblem->toDebuggableArray());`

### Default Mode (Laravel 11)

To use it, add the following code to the Exception Closure in the `bootstrap/app.php` file:

```php
use Pedrosalpr\LaravelApiProblem\LaravelApiProblem;

    ...

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $this->shouldReturnJson($request, $e)) {
                $apiProblem = new LaravelApiProblem($e, $request);
                return $apiProblem->render();
            }
        });
    })...

```

#### Creating Exceptions in the Api Problem pattern

There is the possibility of creating exceptions that extend `LaravelApiProblemException`.

This already makes it easier to transform the exception into the Api Problem pattern.

To do this, simply run the following command:

`php artisan laravel-api-problem:exception {name}`

For example: 

`php artisan laravel-api-problem:exception DummyException`

This creates a class exception inside `Exceptions\ApiProblem`;

```php
<?php

namespace App\Exceptions\ApiProblem;

use Pedrosalpr\LaravelApiProblem\Exceptions\LaravelApiProblemException;

class DummyException extends LaravelApiProblemException
{

}
```

### Custom Mode

If you want to customize an `Api Problem` class to add your guidelines for which error responses should be returned, simply extend the class with the following command:

`php artisan laravel-api-problem:extend`

For example:

`php artisan laravel-api-problem:extend DummyApiProblem`

This creates a class Api Problem inside `ApiProblem`;

```php
<?php

namespace App\ApiProblem;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Pedrosalpr\LaravelApiProblem\Http\LaravelHttpApiProblem;
use Pedrosalpr\LaravelApiProblem\LaravelApiProblem;

class DummyApiProblem extends LaravelApiProblem
{
    public function __construct(
        protected \Throwable $exception,
        protected Request $request
    ) {
        match (get_class($exception)) {
            \Exception::class => $this->dummy(),
            default => parent::__construct($exception, $request)
        };
    }

    protected function dummy()
    {
        $extensions = [
            'errors' => "Dummy",
        ];
        $this->apiProblem = new LaravelHttpApiProblem(
            Response::HTTP_I_AM_A_TEAPOT,
            $this->exception->getMessage(),
            $this->getUriInstance(),
            $extensions
        );
    }
}
```

And within the match, add the names of the exceptions classes with their respective methods, such as `dummy()`.

#### Older Version Laravel 9 and 10

In the `Handler.php` file replace the `LaravelApiProblem` object instance to `DummyApiProblem`.

```php
    $this->renderable(function (\Throwable $e, Request $request) {
        if ($request->is('api/*') || $this->shouldReturnJson($request, $e)) {
            $apiProblem = new DummyApiProblem($e, $request);
            return $apiProblem->render();
        }
    });
```

#### Laravel 11

Add the following code to the Exception Closure in the `bootstrap/app.php` file:

```php
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $this->shouldReturnJson($request, $e)) {
                $apiProblem = new DummyApiProblem($e, $request);
                return $apiProblem->render();
            }
        });
    })
```

## Testing

TODO

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Leandro Pedrosa Rodrigues](https://github.com/pedrosalpr)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
