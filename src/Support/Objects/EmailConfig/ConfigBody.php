<?php
namespace Apsonex\EmailBuilderPhp\Support\Objects\EmailConfig;

use Illuminate\Contracts\Support\Arrayable;

class ConfigBody implements Arrayable
{
    public string $id;
    public string $type;
    public array $config;
    public array $content;

    public function __construct(string $id, string $type, array $config, array $content)
    {
        $this->id = $id;
        $this->type = $type;
        $this->config = $config;
        $this->content = $content;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            type: $data['type'] ?? '',
            config: $data['config'] ?? [],
            content: $data['content'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'config' => $this->config,
            'content' => $this->content,
        ];
    }
}
