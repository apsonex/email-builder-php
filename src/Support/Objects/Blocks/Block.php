<?php
namespace Apsonex\EmailBuilderPhp\Support\Objects\Blocks;

use Illuminate\Contracts\Support\Arrayable;

class Block implements Arrayable
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $type,
        public string $category,
        public string $categoryLabel,
        public ?string $description,
        public array $config
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            slug: $data['slug'] ?? '',
            type: $data['type'] ?? '',
            category: $data['category'] ?? '',
            categoryLabel: $data['categoryLabel'] ?? '',
            description: $data['description'] ?? null,
            config: $data['config'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'category' => $this->category,
            'categoryLabel' => $this->categoryLabel,
            'description' => $this->description,
            'config' => $this->config,
        ];
    }
}
