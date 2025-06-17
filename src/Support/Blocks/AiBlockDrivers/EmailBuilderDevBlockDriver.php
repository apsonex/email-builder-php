<?php

namespace Apsonex\EmailBuilderPhp\Support\Blocks\AiBlockDrivers;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Apsonex\EmailBuilderPhp\Enums\AiTool;
use GuzzleHttp\Exception\RequestException;
use Apsonex\EmailBuilderPhp\Enums\AiProvider;

class EmailBuilderDevBlockDriver
{
    protected string $endPoint = 'https://emailbuilder.dev/api/ai-block-builder';
    protected string $token;
    protected array $payload;
    protected bool $fake = false;
    protected int $timeout = 60;

    /**
     * @var callable|null
     */
    protected $onError = null;

    public function endpoint(string $url): static
    {
        $this->endPoint = $url;
        return $this;
    }

    public function timeout(int $timeout): static
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function fake(): static
    {
        $this->fake = true;
        return $this;
    }

    public function onError(callable $callback): static
    {
        $this->onError = $callback;
        return $this;
    }

    public function prepare(array $data): static
    {
        // Validate provider
        if (
            !isset($data['provider']) ||
            !AiProvider::tryFrom($data['provider']) ||
            !isset($data['ai_model']) ||
            !isset($data['provider_api_key'])
        ) {
            throw new InvalidArgumentException("Invalid AI provider, model or api key");
        }

        // Validate tools if present
        $tools = $data['tools'] ?? [];
        foreach ($tools as $tool) {
            if (!AiTool::tryFrom($tool)) {
                throw new InvalidArgumentException("Invalid AI tool: {$tool}");
            }
        }

        $this->token = $data['token'];

        $this->timeout($data['timeout'] ?? $this->timeout);

        $this->payload = collect([
            'category'         => $data['category'],
            'provider'         => $data['provider'],
            'provider_api_key' => $data['provider_api_key'],
            'ai_model'         => $data['ai_model'],
            'user_prompt'      => $data['user_prompt'] ?? '',
            'count'            => $data['count'] ?? 1,
            'timeout'          => $data['timeout'] ?? $this->timeout,
            'project_id'       => $data['project_id'] ?? null,
            'org_id'           => $data['org_id'] ?? null,
            'tools'            => empty($tools) ? null : $tools,
        ])->filter()->toArray();

        return $this;
    }

    public function query(): ?array
    {
        try {
            $client = new Client([
                'timeout' => $this->timeout,
                'headers' => array_filter([
                    'Authorization'     => sprintf('Bearer %s', $this->token),
                    'Accept'            => 'application/json',
                    'x-fake-response'   => $this->fake ? 'true' : null,
                ]),
            ]);

            $response = $client->post($this->endPoint, [
                'json' => $this->payload,
            ]);

            $body = json_decode((string) $response->getBody(), true);

            return is_array($body) ? $body : null;
        } catch (RequestException $e) {
            if ($this->onError) {
                call_user_func($this->onError, $e);
            }

            return null;
        }
    }
}
