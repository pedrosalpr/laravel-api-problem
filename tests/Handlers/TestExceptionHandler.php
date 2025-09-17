<?php

declare(strict_types=1);

namespace Pedrosalpr\LaravelApiProblem\Tests\Handlers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Pedrosalpr\LaravelApiProblem\Http\LaravelHttpApiProblem;
use Throwable;

class TestExceptionHandler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        // Define um valor de instância padrão para todos os problemas
        $instance = $request->fullUrl();

        if ($e instanceof AuthenticationException) {
            $problem = new LaravelHttpApiProblem(Response::HTTP_UNAUTHORIZED, $e->getMessage(), $instance);

            return response()->json($problem->toArray(), $problem->getStatusCode())
                ->withHeaders(['Content-Type' => $problem->getHeaderProblemJson()]);
        }

        if ($e instanceof AuthorizationException) {
            $problem = new LaravelHttpApiProblem(Response::HTTP_FORBIDDEN, $e->getMessage(), $instance);

            return response()->json($problem->toArray(), $problem->getStatusCode())
                ->withHeaders(['Content-Type' => $problem->getHeaderProblemJson()]);
        }

        // As exceções Http (como 404) precisam ser tratadas para que o cabeçalho seja `application/problem+json`
        if ($this->isHttpException($e)) {
            $statusCode = $e->getStatusCode();
            $title = Response::$statusTexts[$statusCode] ?? 'Unknown Error';
            $problem = new LaravelHttpApiProblem($statusCode, $e->getMessage(), $instance, title: $title);

            return response()->json($problem->toArray(), $problem->getStatusCode())
                ->withHeaders(['Content-Type' => $problem->getHeaderProblemJson()]);
        }

        if ($e instanceof ValidationException) {
            $problem = new LaravelHttpApiProblem(
                statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
                detail: 'The given data was invalid.',
                instance: $instance,
                extensions: ['errors' => $e->errors()]
            );

            return response()->json($problem->toArray(), $problem->getStatusCode())
                ->withHeaders(['Content-Type' => $problem->getHeaderProblemJson()]);
        }

        $problem = new LaravelHttpApiProblem(
            statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
            detail: 'An internal server error occurred.', // Mensagem genérica por segurança
            instance: $instance
        );

        return response()->json($problem->toArray(), $problem->getStatusCode())
            ->withHeaders(['Content-Type' => $problem->getHeaderProblemJson()]);
    }
}
