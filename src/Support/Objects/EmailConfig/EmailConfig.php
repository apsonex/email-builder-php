<?php

namespace Apsonex\EmailBuilderPhp\Support\Objects\EmailConfig;

use Illuminate\Contracts\Support\Arrayable;

class EmailConfig implements Arrayable
{
    public string $name;
    public string $type;
    public string $industry;
    public string $category;
    public ConfigHead $head;
    public ConfigBody $body;

    public function __construct(
        string $name,
        string $type,
        string $industry,
        string $category,
        ConfigHead $head,
        ConfigBody $body
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->industry = $industry;
        $this->category = $category;
        $this->head = $head;
        $this->body = $body;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            type: $data['type'],
            industry: $data['industry'],
            category: $data['category'],
            head: ConfigHead::fromArray($data['config']['head']),
            body: ConfigBody::fromArray($data['config']['body']),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'industry' => $this->industry,
            'category' => $this->category,
            'config' => [
                'head' => $this->head->toArray(),
                'body' => $this->body->toArray(),
            ],
        ];
    }
}
