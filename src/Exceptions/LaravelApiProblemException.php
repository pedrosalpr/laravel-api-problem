<?php

declare(strict_types=1);

namespace Pedrosalpr\LaravelApiProblem\Exceptions;

use Pedrosalpr\LaravelApiProblem\Http\LaravelHttpApiProblem;

class LaravelApiProblemException extends \Exception
{
    public function __construct(
        protected int $statusCode,
        protected string $detail,
        protected string $instance,
        protected array $extensions = [],
        protected ?string $title = null,
        protected string $type = LaravelHttpApiProblem::TYPE_ABOUT_BLANK,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($detail, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getDetail(): string
    {
        return $this->detail;
    }

    public function getInstance(): string
    {
        return $this->instance;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
