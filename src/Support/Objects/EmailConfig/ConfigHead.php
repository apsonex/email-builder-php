<?php
namespace Apsonex\EmailBuilderPhp\Support\Objects\EmailConfig;

use Illuminate\Contracts\Support\Arrayable;

class ConfigHead implements Arrayable
{
    /** @var FontConfig[] */
    public array $fonts;

    public string $breakpoint;

    public function __construct(string $breakpoint, array $fonts)
    {
        $this->breakpoint = $breakpoint;
        $this->fonts = $fonts;
    }

    public static function fromArray(array $data): self
    {
        $fonts = array_map(fn($font) => FontConfig::fromArray($font), $data['fonts'] ?? []);
        return new self(
            breakpoint: $data['breakpoint'] ?? '',
            fonts: $fonts,
        );
    }

    public function toArray(): array
    {
        return [
            'breakpoint' => $this->breakpoint,
            'fonts' => array_map(fn(FontConfig $font) => $font->toArray(), $this->fonts),
        ];
    }
}
