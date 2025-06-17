<?php

namespace Apsonex\EmailBuilderPhp\Support\EmailConfigs;

use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Contracts\DbEmailConfigDriverContract;
use Apsonex\EmailBuilderPhp\Support\EmailConfigs\DbEmailConfigDrivers\SqliteDriver;

/**
 * @method array index(array $filters = [])
 * @method ?array show(array $filters)
 * @method array|bool store(array $data)
 * @method bool update(array $whereClauses, array $data)
 * @method bool destroy(array $whereClauses)
 */
class DbEmailConfig
{
    use Makebale;

    public static string $defaultDriver = SqliteDriver::class;

    protected ?string $driver = null;

    protected ?DbEmailConfigDriverContract $driverInstance = null;

    public static bool $multitenancyEnabled = false;

    public static string $tenantKeyName = 'tenant_id';

    public static string $ownerKeyName = 'owner_id';

    protected array $preparationData = [];

    public function driver(string $driver): static
    {
        $this->driver = $driver;
        return $this;
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

    public static function ownerKeyName(string $ownerKeyName = 'owner_id'): void
    {
        self::$ownerKeyName = $ownerKeyName;
    }

    public function preparation(array $preparationData): static
    {
        $this->preparationData = $preparationData;
        return $this;
    }

    public function getPreparationData(): array
    {
        return [
            'multitenancyEnabled' => static::$multitenancyEnabled,
            'tenantKeyName' => static::$tenantKeyName,
            'ownerKeyName' => static::$ownerKeyName,
            ...$this->preparationData,
        ];
    }

    public function __call($method, $args)
    {
        $this->initializeDriver();

        return $this->driverInstance
            ->tableName('email_configurations')
            ->prepare($this->getPreparationData())
            ->{$method}(...$args);
    }

    protected function initializeDriver(): void
    {
        $driverClass = $this->driverInstance ? get_class($this->driverInstance) : null;

        if ($driverClass && $driverClass !== $this->driver) {
            $driver = $this->driver;
            $this->driverInstance = new $driver();
            return;
        }

        if (!$driverClass) {
            $driver = $this->driver ?: static::$defaultDriver;
            $this->driverInstance = new $driver();
        }
    }
}
