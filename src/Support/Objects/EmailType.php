<?php

namespace Apsonex\EmailBuilderPhp\Support\Objects;

use Illuminate\Contracts\Support\Arrayable;

class EmailType implements Arrayable
{
    public function __construct(
        public string $slug,
        public string $label
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            slug: $data['slug'],
            label: $data['label']
        );
    }

    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'label' => $this->label,
        ];
    }
}
