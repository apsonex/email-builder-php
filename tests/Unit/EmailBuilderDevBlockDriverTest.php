<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Http;
use Apsonex\EmailBuilderPhp\Enums\AiTool;
use GuzzleHttp\Exception\RequestException;
use Apsonex\EmailBuilderPhp\Enums\AiProvider;
use Apsonex\EmailBuilderPhp\Support\Blocks\AiBlockDrivers\EmailBuilderDevBlockDriver;

it('can set endpoint, timeout, and fake mode', function () {
    $driver = new EmailBuilderDevBlockDriver();

    $driver->endpoint('https://custom-endpoint.test')
           ->timeout(120)
           ->fake();

    expect($driver)->toMatchObject([
        'endPoint' => 'https://custom-endpoint.test',
        'timeout'  => 120,
        'fake'     => true,
    ]);
});

it('throws exception for invalid provider', function () {
    $driver = new EmailBuilderDevBlockDriver();

    $driver->prepare([
        'provider' => 'invalid-provider',
        'token' => 'test-token',
        'provider_api_key' => 'xxx',
        'category' => 'hero',
        'ai_model' => 'gpt-4',
        'count' => 1,
        'timeout' => 60,
        'tools' => [],
    ]);
})->throws(InvalidArgumentException::class, 'Invalid AI provider');

it('throws exception for invalid tool', function () {
    $driver = new EmailBuilderDevBlockDriver();

    $driver->prepare([
        'provider' => AiProvider::OpenAI->value,
        'token' => 'test-token',
        'provider_api_key' => 'xxx',
        'category' => 'hero',
        'ai_model' => 'gpt-4',
        'count' => 1,
        'timeout' => 60,
        'tools' => ['invalid_tool'],
    ]);
})->throws(InvalidArgumentException::class, 'Invalid AI tool');

it('sends a request and returns decoded response', function () {
    Http::fake([
        'https://example-mcp-server.com/create-block' => Http::response([
            'status' => 'ok',
            'blocks' => [['name' => 'Hero Block']]
        ], 200),
    ]);

    $driver = new EmailBuilderDevBlockDriver();
    $driver->endpoint('https://example-mcp-server.com/create-block')
           ->fake()
           ->prepare([
               'provider' => AiProvider::OpenAI->value,
               'token' => 'test-token',
               'provider_api_key' => 'xxx',
               'category' => 'hero',
               'ai_model' => 'gpt-4',
               'count' => 1,
               'timeout' => 60,
               'tools' => [AiTool::StockImageSearch->value],
           ]);

    $result = $driver->query();

    expect($result)
        ->toBeArray()
        ->and($result['status'])->toBe('ok')
        ->and($result['blocks'])->toBeArray();
});

it('calls onError callback on failure', function () {
    $called = false;

    $driver = new EmailBuilderDevBlockDriver();
    $driver->endpoint('https://invalid-endpoint.test')
           ->prepare([
               'provider' => AiProvider::OpenAI->value,
               'token' => 'test-token',
               'provider_api_key' => 'xxx',
               'category' => 'hero',
               'ai_model' => 'gpt-4',
               'count' => 1,
               'timeout' => 1,
               'tools' => [],
           ])
           ->onError(function ($e) use (&$called) {
               $called = true;
               expect($e)->toBeInstanceOf(RequestException::class);
           });

    // Simulate timeout by pointing to an unreachable URL or mocking Guzzle if preferred
    $result = $driver->query();

    expect($result)->toBeNull();
    expect($called)->toBeTrue();
});
