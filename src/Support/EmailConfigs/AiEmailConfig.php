<?php

namespace Apsonex\EmailBuilderPhp\Support\EmailConfigs;

class AiEmailConfig
{

    protected ?string $defaultDriver = null;

    protected ?string $driver = null;

    public function driver(string $driver): static
    {
        $this->driver = $driver;
        return $this;
    }
}
