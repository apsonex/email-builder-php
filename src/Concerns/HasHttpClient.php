<?php

namespace Apsonex\EmailBuilderPhp\Concerns;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

trait HasHttpClient
{
    protected string $endpoint;

    protected ?string $token = null;

    protected array $clientOptions = [];

    public function endpoint(string $endpoint): static
    {
        $this->endpoint = rtrim($endpoint, '/');
        return $this;
    }

    public function token(string $token): static
    {
        $this->token = $token;
        return $this;
    }

    public function withClientOptions(array $options): static
    {
        $this->clientOptions = $options;
        return $this;
    }

    protected function httpClient(): Client
    {
        $default = [
            'base_uri' => $this->endpoint,
            'headers' => array_filter([
                'Authorization' => $this->token ? 'Bearer ' . $this->token : null,
                'Accept'        => 'application/json',
            ]),
            'timeout' => 30.0,
        ];

        return new Client(array_replace_recursive($default, $this->clientOptions));
    }

    public function query(string $method = 'GET', array $payload = [])
    {
        $url = $payload['url'] ?? null;
        if (!$url) {
            throw new \InvalidArgumentException('Missing URL in payload.');
        }

        try {
            $client = $this->httpClient();

            $response = $client->request($method, $url, [
                'json' => $payload['data'] ?? [],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $body = $e->getResponse()?->getBody()?->getContents();
            throw new \RuntimeException("Request failed: {$e->getMessage()} - {$body}");
        }
    }
}
