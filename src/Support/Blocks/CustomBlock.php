<?php

namespace Apsonex\EmailBuilderPhp\Support\Blocks;

use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Contracts\CustomBlockContract;
use Apsonex\EmailBuilderPhp\Support\Blocks\CustomBlockDrivers\FileCustomBlockDriver;

/**
 * @method array index(array $filters = [])
 * @method ?array show(string $id)
 * @method array|bool store(array $data)
 * @method bool update(string $id, array $data)
 * @method bool destroy(string $id)
 */
class CustomBlock
{
    use Makebale;

    public static $defaultDriver = FileCustomBlockDriver::class;

    protected ?string $driver = null;

    protected ?CustomBlockContract $driverInstance = null;

    public static bool $multitenancyEnabled = false;

    public static string $tenantKeyName = 'tenant_id';

    public static string $ownerKeyName = 'owner_id';

    protected array $preperationData = [];

    public function driver(string $driver): static
    {
        $this->driver = $driver;
        return $this;
    }

    public static function ownerKeyName(string $ownerKeyName = 'owner_id'): void
    {
        self::$ownerKeyName = $ownerKeyName;
    }

    public static function enableMultitenancy(string $tenantKeyName): void
    {
        self::$multitenancyEnabled = true;
        self::$tenantKeyName = $tenantKeyName;
    }

    public static function disableMultitenancy(): void
    {
        self::$multitenancyEnabled = false;
    }

    public static function isMultitenant(): bool
    {
        return self::$multitenancyEnabled;
    }

    public function preperation($preperationData): static
    {
        $this->preperationData = $preperationData;
        return $this;
    }

    public function getPreperationData(): array
    {
        return [
            'multitenancyEnabled' => static::$multitenancyEnabled,
            'tenantKeyName' => static::$tenantKeyName,
            'ownerKeyName' => static::$ownerKeyName,
            ...(is_array($this->preperationData) ? $this->preperationData : []),
        ];
    }

    public function __call($method, $args)
    {
        $this->initilizeDriver();

        return $this->driverInstance->prepare($this->getPreperationData())->{$method}(...$args);
    }

    protected function initilizeDriver()
    {
        $driverClass = $this->driverInstance ? get_class($this->driverInstance) : null;

        if ($driverClass && $driverClass !== $this->driver) {
            $driver = $this->driver;
            $this->driverInstance = (new $driver());
            return;
        }

        if (!$driverClass) {
            $driver = ($this->driver ?: static::$defaultDriver);
            $this->driverInstance = (new $driver());
            return;
        }
    }
}
