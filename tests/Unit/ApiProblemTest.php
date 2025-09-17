<?php

declare(strict_types=1);

use Pedrosalpr\LaravelApiProblem\Http\LaravelHttpApiProblem;

test('it can be instantiated with required arguments', function () {
    $problem = new LaravelHttpApiProblem(
        statusCode: 404,
        detail: 'The resource was not found.',
        instance: 'https://example.com/api/posts/1'
    );

    expect($problem->getStatusCode())->toBe(404);
    expect($problem->getDetail())->toBe('The resource was not found.');
    expect($problem->getInstance())->toBe('https://example.com/api/posts/1');
    expect($problem->getTitle())->toBe('Not Found');
    expect($problem->getType())->toBe('about:blank');
});

test('it sets title and type automatically based on status code', function () {
    $problem = new LaravelHttpApiProblem(
        statusCode: 401,
        detail: 'Authentication failed.',
        instance: 'https://example.com/api/protected'
    );

    expect($problem->getTitle())->toBe('Unauthorized');
    expect($problem->getType())->toBe('about:blank');
});

test('toArray method returns valid problem json structure', function () {
    $problem = new LaravelHttpApiProblem(
        statusCode: 422,
        detail: 'The request payload is invalid.',
        instance: 'https://example.com/api/users',
        extensions: ['errors' => ['name' => ['The name field is required.']]]
    );

    $array = $problem->toArray();

    expect($array)
        ->toHaveKeys(['status', 'type', 'title', 'detail', 'instance', 'timestamp', 'errors'])
        ->and($array['status'])->toBe(422)
        ->and($array['title'])->toBe('Unprocessable Entity')
        ->and($array['errors']['name'])->toBe(['The name field is required.']);
});

test('it defaults status code to 400 if out of range', function (int $statusCode) {
    $problem = new LaravelHttpApiProblem(
        statusCode: $statusCode,
        detail: 'Invalid status code.',
        instance: 'https://example.com/api'
    );

    expect($problem->getStatusCode())->toBe(400);
})->with([
    100,
    600,
]);

test('it includes extensions in the problem detail', function () {
    $extensions = [
        'trace_id' => '1a2b3c4d5e',
        'app_code' => 'AUTH-001',
    ];

    $problem = new LaravelHttpApiProblem(
        statusCode: 401,
        detail: 'Unauthorized access.',
        instance: 'https://example.com/api/protected',
        extensions: $extensions
    );

    $array = $problem->toArray();

    expect($array)->toHaveKeys(['trace_id', 'app_code']);
    expect($array['trace_id'])->toBe('1a2b3c4d5e');
});
