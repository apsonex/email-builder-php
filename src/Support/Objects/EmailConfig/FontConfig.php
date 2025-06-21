<?php

namespace Apsonex\EmailBuilderPhp\Support\Objects\EmailConfig;

use Illuminate\Contracts\Support\Arrayable;

class FontConfig implements Arrayable
{
    public string $key;
    public string $provider;
    public string $category;
    public string $family;
    public string $urlString;

    public function __construct(string $key, string $provider, string $category, string $family, string $urlString)
    {
        $this->key = $key;
        $this->provider = $provider;
        $this->category = $category;
        $this->family = $family;
        $this->urlString = $urlString;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['key'] ?? '',
            $data['provider'] ?? '',
            $data['category'] ?? '',
            $data['family'] ?? '',
            $data['urlString'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'provider' => $this->provider,
            'category' => $this->category,
            'family' => $this->family,
            'urlString' => $this->urlString,
        ];
    }
}
