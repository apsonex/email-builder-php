<?php

namespace Apsonex\EmailBuilderPhp\Support\Blocks\AiBlockDrivers;

use GuzzleHttp\Client;
use Illuminate\Contracts\Support\Arrayable;
use Apsonex\EmailBuilderPhp\Contracts\StreamedResponse;
use Apsonex\EmailBuilderPhp\Contracts\HttpQueryDriverContract;

class EmailBuilderDevDriver extends BaseDriver implements HttpQueryDriverContract
{
    /**
     * Stream AI-generated email config response from the API.
     *
     * This method returns a StreamedResponse object which can be used in Laravel
     * or any other PHP framework/application.
     *
     * 🚀 Usage in Laravel:
     * ---------------------
     * ```php
     * use Illuminate\Http\Request;
     * use Apsonex\EmailBuilderPhp\Support\Blocks\AiBlockDrivers\EmailBuilderDevDriver;
     *
     * public function streamEmailConfig(Request $request)
     * {
     *     $driver = new EmailBuilderDevDriver(token: 'your_api_key');
     *     $response = $driver->query([...]); // your payload
     *
     *     return response()->stream(
     *         fn () => $response->send(),
     *         $response->status,
     *         $response->headers
     *     );
     * }
     * ```
     *
     * 🧱 Usage in Vanilla PHP:
     * ------------------------
     * ```php
     * use Apsonex\EmailBuilderPhp\Support\EmailConfigs\AiEmailConfigDrivers\EmailBuilderDevDriver;
     *
     * $driver = new EmailBuilderDevDriver(token: 'your_api_key');
     * $response = $driver->query([...]);
     * $response->send();
     * ```
     *
     * @param array|Arrayable $payload
     * @return StreamedResponse
     */
    public function query(array|Arrayable $payload): StreamedResponse
    {
        $url = "/ai/block-configs/create";

        $default = [
            'base_uri' => $this->endpoint ?: static::$defaultEndpoint,
            'headers' => array_filter([
                'Authorization' => $this->token ? 'Bearer ' . $this->token : null,
                'Accept'        => 'application/json',
                ...($this->fake ? [
                    'X-Fake' => 'true',
                    'X-Fake-Type' => '200',
                    'X-Fake-Speed' => '1',
                ] : []),
            ]),
            'timeout' => 30.0,
        ];

        $client = new Client(array_replace_recursive($default, $this->clientOptions));

        $response = $client->request('POST', $url, [
            'json' => $payload instanceof Arrayable ? $payload->toArray() : $payload,
            'stream' => true,
        ]);

        return new StreamedResponse(
            stream: $response->getBody(),
            headers: [
                'Content-Type' => 'application/json',
                'X-Accel-Buffering' => 'no',
            ],
            status: $response->getStatusCode(),
        );
    }
}
