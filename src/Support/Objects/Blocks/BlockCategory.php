<?php

namespace Apsonex\EmailBuilderPhp\Support\Objects\Blocks;

use Illuminate\Contracts\Support\Arrayable;

class BlockCategory implements Arrayable
{
    public function __construct(
        public string $label,
        public string $slug,
        public ?string $description = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            label: $data['label'] ?? '',
            slug: $data['slug'] ?? '',
            description: $data['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'slug' => $this->slug,
            'description' => $this->description,
        ];
    }
}
