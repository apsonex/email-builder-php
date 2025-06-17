<?php

namespace Apsonex\EmailBuilderPhp\Support\EmailConfigs\EmailConfigDrivers;

class EmailBuilderDevDriver extends BaseEmailConfigDriver
{
    protected static string $endpoint = 'https://emailbuilder.dev/api/email-builder/ai/actions/blocks/create';

    protected bool $fake = false;
    protected int $fakeType = 200;

    public function fake(int $fakeType = 200): static
    {
        $this->fake = true;
        $this->fakeType = in_array($fakeType, static::availableFakeTypes()) ? $fakeType : 200;
        return $this;
    }

    public function query(array $payload, array $headers = []): array
    {
        $data = [
            'category'         => $payload['category'],
            'provider'         => $payload['provider'],
            'provider_api_key' => $payload['provider_api_key'],
            'ai_model'         => $payload['ai_model'],
            'user_prompt'      => $payload['user_prompt'],
            'count'            => $payload['count'],
            'timeout'          => $payload['timeout'],
            'project_id'       => $payload['project_id'] ?? null,
            'org_id'           => $payload['org_id'] ?? null,
            'tools'            => ['stock_image_search'],
        ];

        $headers[] = 'Authorization: Bearer ' . $payload['token'];
        $headers[] = 'Content-Type: application/json';

        if ($this->fake) {
            $headers[] = 'x-fake: true';
            $headers[] = 'x-fake-code: ' . $this->fakeType;
        }

        $ch = curl_init(static::$endpoint);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => $payload['timeout'] ?? 30,
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            curl_close($ch);
            return [
                'status' => 'error',
                'code'   => 500,
                'error'  => curl_error($ch),
            ];
        }

        curl_close($ch);

        $json = json_decode($result, true);

        if ($httpCode >= 200 && $httpCode < 300 && is_array($json)) {
            return [
                'status' => 'success',
                'code'   => $httpCode,
                ...$json,
            ];
        }

        return [
            'status' => 'error',
            'code'   => $httpCode,
            'response' => $result,
        ];
    }

    public static function availableFakeTypes(): array
    {
        return [200];
    }
}
