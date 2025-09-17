<?php

declare(strict_types=1);

namespace Pedrosalpr\LaravelApiProblem\Http;

use Illuminate\Support\Carbon;
use Pedrosalpr\LaravelApiProblem\LaravelApiProblemInterface;

class LaravelHttpApiProblem implements LaravelApiProblemInterface
{
    public const TYPE_ABOUT_BLANK = 'about:blank';

    private const HEADER_PROBLEM_JSON = 'application/problem+json';

    private static $statusTitles = [
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        419 => 'Page Expired',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'Connection Closed Without Response',
        451 => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    private Carbon $timestamp;

    public function __construct(
        private int $statusCode,
        private string $detail,
        private string $instance,
        private array $extensions = [],
        private ?string $title = null,
        private string $type = self::TYPE_ABOUT_BLANK
    ) {
        if ($this->statusCode < 400 || $this->statusCode > 599) {
            $this->statusCode = 400;
        }
        if (!filter_var($this->type, FILTER_VALIDATE_URL) || empty($this->title)) {
            $this->title = $this->getTitleForStatusCode($this->statusCode);
            $this->type = self::TYPE_ABOUT_BLANK;
        }
        $this->timestamp = Carbon::now();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDetail(): string
    {
        return $this->detail;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getInstance(): string
    {
        return $this->instance;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function toArray(): array
    {
        return array_merge(
            [
                'status' => $this->statusCode,
                'type' => $this->type,
                'title' => $this->title,
                'detail' => $this->detail,
                'instance' => $this->instance,
                'timestamp' => $this->timestamp->toJSON()
            ],
            $this->extensions
        );
    }

    public function getHeaderProblemJson(): string
    {
        return self::HEADER_PROBLEM_JSON;
    }

    private function getTitleForStatusCode(int $statusCode): string
    {
        return self::$statusTitles[$statusCode] ?? 'Unknown';
    }
}
