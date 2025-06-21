<?php

namespace Apsonex\EmailBuilderPhp\Support;

use GuzzleHttp\Client;

class Asset
{
    static string $endpoint = 'https://ai.emailbuilder.dev';

    public static function manifest(array $clientOptions = []): ?array
    {
        $default = [
            'base_uri' => static::$endpoint,
            'headers' => array_filter(['Accept' => 'application/json']),
            'timeout' => 10.0,
        ];

        $client = new Client(array_replace_recursive($default, $clientOptions));

        try {
            $response = $client->request('GET', '/assets-url-manifest.json');

            $response = json_decode($response->getBody()->getContents(), true);

            if (($response['status'] ?? null) !== 'success') {
                return [
                    'status' => 'error',
                    'manifest' => null,
                    'message' => 'Invalid response',
                ];
            }

            return [
                'status' => 'success',
                'manifest' => $response['manifest'] ?? [],
            ];
        } catch (\Throwable $e) {
            dd($e->getMessage());
            return [
                'status' => 'error',
                'manifest' => null,
                'message' => $e->getMessage(),
            ];
        }
    }
}
