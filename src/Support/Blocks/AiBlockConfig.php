<?php

namespace Apsonex\EmailBuilderPhp\Support\Blocks;

use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Contracts\HttpQueryDriverContract;
use Apsonex\EmailBuilderPhp\Support\Blocks\AiBlockDrivers\EmailBuilderDevDriver;

class AiBlockConfig
{
    use Makebale;

    protected ?string $defaultDriver = EmailBuilderDevDriver::class;

    protected ?string $driver = null;

    protected ?HttpQueryDriverContract $driverInstance = null;

    public function driver(?string $driver = null): HttpQueryDriverContract
    {
        if ($this->driverInstance) return $this->driverInstance;

        $this->driver = $driver ?: $this->defaultDriver;

        return $this->driverInstance = $this->driver::make();
    }
}
