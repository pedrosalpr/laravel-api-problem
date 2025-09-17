<?php

declare(strict_types=1);

namespace Pedrosalpr\LaravelApiProblem;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Pedrosalpr\LaravelApiProblem\Exceptions\LaravelApiProblemException;
use Pedrosalpr\LaravelApiProblem\Http\LaravelHttpApiProblem;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class LaravelApiProblem
{
    protected LaravelApiProblemInterface $apiProblem;

    protected LaravelApiProblemException $apiProblemException;

    public function __construct(
        protected \Throwable $exception,
        protected Request $request
    ) {
        if ($exception instanceof LaravelApiProblemException) {
            $this->apiProblemException = $exception;
            $this->apiProblemException();

            return;
        }
        match (get_class($exception)) {
            ValidationException::class => $this->validation(),
            \UnhandledMatchError::class,\Exception::class => $this->default(),
            HttpException::class => $this->default(419),
            AuthorizationException::class => $this->default(403),
            default => $this->default()
        };
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json(
            $this->apiProblem->toArray(),
            $this->apiProblem->getStatusCode(),
            [
                'content-type' => $this->apiProblem->getHeaderProblemJson(),
            ]
        );
    }

    /**
     * Debug the class in array to view more details, such as: api problem and exception
     */
    public function toDebuggableArray(): array
    {
        $previousMessages = [];
        $previous = $this->exception;
        while ($previous = $previous->getPrevious()) {
            $previousMessages[] = $this->serializeException($previous);
        }

        return array_merge(
            $this->apiProblem->toArray(),
            [
                'exception' => array_merge(
                    $this->serializeException($this->exception),
                    [
                        'previous' => $previousMessages,
                    ]
                ),
            ]
        );
    }

    /**
     * Transform any exception into an http api problem with status code
     */
    protected function default(?int $statusCode = null): void
    {
        $statusCode = $this->getStatusCode($statusCode);
        $extensions = $this->getContextExceptionAsExtensions();
        $this->apiProblem = new LaravelHttpApiProblem(
            $statusCode,
            $this->exception->getMessage() ?: get_class($this->exception),
            $this->getUriInstance(),
            $extensions
        );
    }

    /**
     * Transforms a validation exception into an http api problem, and adds the errors as an extension
     */
    protected function validation(): void
    {
        $extensions = [
            'errors' => ($this->exception instanceof ValidationException)
                ? $this->exception->errors()
                : null,
        ];
        $this->apiProblem = new LaravelHttpApiProblem(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->exception->getMessage(),
            $this->getUriInstance(),
            $extensions
        );
    }

    /**
     * Transforms an exception api problem into an Http Api Problem
     */
    protected function apiProblemException(): void
    {
        $this->apiProblem = new LaravelHttpApiProblem(
            $this->apiProblemException->getStatusCode(),
            $this->apiProblemException->getDetail(),
            $this->apiProblemException->getInstance(),
            $this->apiProblemException->getExtensions(),
            $this->apiProblemException->getTitle(),
            $this->apiProblemException->getType(),
        );
    }

    /**
     * Get uri as instance
     */
    protected function getUriInstance(): string
    {
        return $this->request->getUri();
    }

    /**
     * Get the context if it exists within the exception and return it as an extension
     */
    protected function getContextExceptionAsExtensions(): array
    {
        $extensions = [];
        if (! method_exists($this->exception, 'context')) {
            return $extensions;
        }
        $context = $this->exception->context();
        if (is_array($context)) {
            $extensions = $context;
        } elseif (! empty($context)) {
            $extensions = [$context];
        }

        return $extensions;
    }

    /**
     * Gets the status code from the exception code, or from the HttpException Interface, otherwise it returns an Internal Server Error
     */
    protected function getStatusCode(?int $code): int
    {
        if ($this->isStatusCodeInternalOrServerError($code)) {
            return $code;
        }
        if ($this->exception instanceof HttpExceptionInterface) {
            return ($this->isStatusCodeInternalOrServerError($this->exception->getStatusCode()))
                ? $this->exception->getStatusCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return ($this->isStatusCodeInternalOrServerError($this->exception->getCode()))
        ? $this->exception->getCode()
        : Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * Checks if the status code is of the integer type and is in the range of Client and Server Errors
     */
    protected function isStatusCodeInternalOrServerError(?int $statusCode): bool
    {
        return is_int($statusCode) && $statusCode >= 400 && $statusCode <= 599;
    }

    /**
     * Serialize the exception into an array
     */
    private function serializeException(\Throwable $throwable): array
    {
        return [
            'type' => get_class($throwable),
            'message' => $throwable->getMessage(),
            'code' => $throwable->getCode(),
            'line' => $throwable->getLine(),
            'file' => $throwable->getFile(),
            'trace' => explode("\n", $throwable->getTraceAsString()),
        ];
    }
}
