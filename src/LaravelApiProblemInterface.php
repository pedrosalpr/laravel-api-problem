<?php

declare(strict_types=1);

namespace Pedrosalpr\LaravelApiProblem;

interface LaravelApiProblemInterface
{
    public function toArray(): array;

    public function getTitle(): string;

    public function getStatusCode(): int;

    public function getType(): string;

    public function getDetail(): string;

    public function getExtensions(): array;

    public function getHeaderProblemJson(): string;

    public function getInstance(): string;
}
