<?php

namespace Apsonex\EmailBuilderPhp\Support\AiPayload;

use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Contracts\ArrayValueContract;

class Provider implements ArrayValueContract
{
    use Makebale;

    protected string $provider;
    protected string $model;

    public function deepseek(?string $model = 'deepseek-chat'): static
    {
        $this->provider = 'deepseek';
        $this->model = $model ?: 'deepseek-chat';
        return $this;
    }

    public function openai(?string $model = 'gpt-4.1'): static
    {
        $this->provider = 'openai';
        $this->model = $model ?: 'gpt-4.1';
        return $this;
    }

    public function xai(?string $model = 'grok-3'): static
    {
        $this->provider = 'xai';
        $this->model = $model ?: 'gpt-4.1';
        return $this;
    }

    public function value(): array
    {
        return [
            'provider' => $this->provider,
            'modelName' => $this->model,
        ];
    }
}
