<?php

namespace Apsonex\EmailBuilderPhp\Support\Objects\Font;

class FontObject
{
    public function __construct(
        public string $key,
        public string $provider,
        public string $category,
        public string $family,
        public string $urlString,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'],
            provider: $data['provider'],
            category: $data['category'],
            family: $data['family'],
            urlString: $data['urlString'],
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
