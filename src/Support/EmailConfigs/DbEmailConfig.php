<?php
namespace Apsonex\EmailBuilderPhp\Support\EmailConfigs;

use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Contracts\DbEmailConfigDriverConract;
use Apsonex\EmailBuilderPhp\Support\EmailConfigs\DbEmailConfigDrivers\SqliteDriver;

class DbEmailConfig
{
    use Makebale;

    protected ?string $defaultDriver = SqliteDriver::class;

    protected ?string $driver = null;

    protected ?DbEmailConfigDriverConract $driverInstance = null;

    public function driver(?string $driver = null): DbEmailConfigDriverConract
    {
        if ($this->driverInstance) return $this->driverInstance;

        $this->driver = $driver ?: $this->defaultDriver;

        return $this->driverInstance = $this->driver::make();
    }
}
