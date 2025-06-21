<?php

namespace Apsonex\EmailBuilderPhp\Support\Blocks\Payload;

use Illuminate\Contracts\Support\Arrayable;
use Apsonex\EmailBuilderPhp\Contracts\ArrayValueContract;
use Apsonex\EmailBuilderPhp\Contracts\StringableValueContract;

class EmailBuilderDev implements Arrayable
{
    public function __construct(
        protected ArrayValueContract $apiKey,
        protected ArrayValueContract $provider, // ["deepseek", "openai", "xai"]
        protected string $category,
        protected ?StringableValueContract $businessInfo,
        protected ?string $prompt = null,
        protected ?string $tone = null,
        protected ?int $maxTokens = 8000,
        protected ?int $maxSteps = 10,
        protected ?int $count = 1,
        protected ?ArrayValueContract $stockImagesProviderApiKeys = null, //["pexels", "pixabay", "unsplash"]
    ) {
        //
    }

    public static function make(
        ArrayValueContract $apiKey,
        ArrayValueContract $provider, // ["deepseek", "openai", "xai"]
        string $category,
        ?StringableValueContract $businessInfo,
        ?string $prompt = null,
        ?string $tone = null,
        ?int $maxTokens = 8000,
        ?int $maxSteps = 10,
        ?int $count = 1,
        ?ArrayValueContract $stockImagesProviderApiKeys = null, //["pexels", "pixabay", "unsplash"]
    ) {
        return new static(
            apiKey: $apiKey,
            provider: $provider,
            category: $category,
            count: $count,
            tone: $tone,
            businessInfo: $businessInfo,
            prompt: $prompt,
            maxTokens: $maxTokens,
            maxSteps: $maxSteps,
            stockImagesProviderApiKeys: $stockImagesProviderApiKeys,
        );
    }

    public function toArray(): array
    {
        return [
            ...$this->apiKey->value(),
            ...$this->provider->value(),
            'category' => $this->category,
            'count' => $this->count,
            'tone' => $this->tone,
            'businessInfo' => trim($this->businessInfo?->value()) ?: '',
            'prompt' => $this->prompt ?: '',
            'maxTokens' => $this->maxTokens ?: 8000,
            'maxSteps' => $this->maxSteps ?: 10,
            'stockImagesProviderApiKeys' => $this->stockImagesProviderApiKeys?->value(),
        ];
    }
}
