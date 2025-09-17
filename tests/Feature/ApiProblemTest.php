<?php

declare(strict_types=1);

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Pedrosalpr\LaravelApiProblem\Tests\Handlers\TestExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

// Antes de cada teste, substitua o manipulador de exceções do Laravel
// pelo seu manipulador de exceções de teste.
beforeEach(function () {
    $this->app->singleton(
        \Illuminate\Contracts\Debug\ExceptionHandler::class,
        TestExceptionHandler::class // Use sua classe de handler de teste aqui
    );
});

test('authentication exception returns 401 problem json', function () {
    // Definir uma rota que lança a exceção diretamente.
    Route::get('/api/protected', fn () => throw new AuthenticationException('Unauthenticated.'));

    getJson('/api/protected')
        ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJson([
            'status' => Response::HTTP_UNAUTHORIZED,
            'title' => 'Unauthorized',
            'detail' => 'Unauthenticated.',
        ]);
});

test('authorization exception returns 403 problem json', function () {
    // Definir uma rota que lança a exceção diretamente.
    Route::get('/api/forbidden', fn () => throw new AuthorizationException('This action is unauthorized.'));

    getJson('/api/forbidden')
        ->assertStatus(Response::HTTP_FORBIDDEN)
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJson([
            'status' => Response::HTTP_FORBIDDEN,
            'title' => 'Forbidden',
            'detail' => 'This action is unauthorized.',
        ]);
});

test('validation exception returns 422 problem json', function () {
    Route::post('/api/validate', function () {
        request()->validate(['email' => 'required|email']);
    });

    postJson('/api/validate', ['email' => 'invalid-email'])
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJson([
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'title' => 'Unprocessable Entity',
            'detail' => 'The given data was invalid.',
        ])
        ->assertJsonStructure([
            'status',
            'title',
            'detail',
            'errors' => [
                'email',
            ],
        ]);
});

test('not found exception returns 404 problem json', function () {
    // A rota não é definida, o Laravel lança a exceção automaticamente
    getJson('/api/non-existent-route')
        ->assertStatus(404)
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJson([
            'status' => 404,
            'title' => 'Not Found',
            'detail' => 'The route api/non-existent-route could not be found.',
        ]);
});

test('method not allowed exception returns 405 problem json', function () {
    // Definir uma rota que só aceita POST
    Route::post('/api/only-post', fn () => ['message' => 'success']);

    // Tentar acessar com o método GET
    getJson('/api/only-post')
        ->assertStatus(Response::HTTP_METHOD_NOT_ALLOWED)
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJson([
            'status' => Response::HTTP_METHOD_NOT_ALLOWED,
            'title' => 'Method Not Allowed',
            'detail' => 'The GET method is not supported for route api/only-post. Supported methods: POST.',
        ]);
});

test('generic exception returns 500 problem json', function () {
    Route::get('/api/internal-error', function () {
        throw new Exception('An internal server error occurred.');
    });

    getJson('/api/internal-error')
        ->assertStatus(500)
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJson([
            'status' => 500,
            'title' => 'Internal Server Error',
            'detail' => 'An internal server error occurred.',
        ]);
});

test('http exception returns correct problem json', function () {
    Route::get('/api/custom-error', function () {
        throw new HttpException(Response::HTTP_UNAUTHORIZED, 'You do not have permission.');
    });

    getJson('/api/custom-error')
        ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJson([
            'status' => Response::HTTP_UNAUTHORIZED,
            'title' => 'Unauthorized',
            'detail' => 'You do not have permission.',
        ]);
});
