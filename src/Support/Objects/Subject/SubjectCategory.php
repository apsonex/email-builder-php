<?php

namespace Apsonex\EmailBuilderPhp\Support\Objects\Subject;

use Illuminate\Contracts\Support\Arrayable;

class SubjectCategory implements Arrayable
{
    public function __construct(
        public string $slug,
        public string $label,
        public array $items // array<string, string> key=slug, value=label/title
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            slug: $data['category']['slug'],
            label: $data['category']['label'],
            items: $data['items'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'label' => $this->label,
            'items' => $this->items,
        ];
    }
}

