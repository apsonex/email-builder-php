<?php

namespace Apsonex\EmailBuilderPhp\Support\EmailConfigs;

use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Contracts\EmailConfigWithAiDriverConract;
use Apsonex\EmailBuilderPhp\Support\EmailConfigs\EmailConfigDrivers\EmailBuilderDevDriver;

class AiEmailConfig
{
    use Makebale;

    protected ?string $defaultDriver = EmailBuilderDevDriver::class;

    protected ?string $driver = null;

    protected ?EmailConfigWithAiDriverConract $driverInstance = null;

    public function driver(?string $driver = null): EmailConfigWithAiDriverConract
    {
        if ($this->driverInstance) return $this->driverInstance;

        $this->driver = $driver ?: $this->defaultDriver;

        return $this->driverInstance = $this->driver::make();
    }
}
