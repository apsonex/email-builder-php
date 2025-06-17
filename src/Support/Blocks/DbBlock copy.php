<?php

namespace Apsonex\EmailBuilderPhp\Support\Blocks;

use Apsonex\EmailBuilderPhp\Concerns\HasTableName;
use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Contracts\DbBlockDriverContract;
use Apsonex\EmailBuilderPhp\Support\Blocks\DbBlockDrivers\{FileDriver};

/**
 * @method array index(array $filters = [])
 * @method ?array show(string $id)
 * @method array|bool store(array $data)
 * @method bool update(string $id, array $data)
 * @method bool destroy(string $id)
 */
class DbBlock
{
    use Makebale, HasTableName;

    public static $defaultDriver = FileDriver::class;

    protected ?string $driver = null;

    protected ?DbBlockDriverContract $driverInstance = null;

    public static bool $multitenancyEnabled = false;

    public static string $tenantKeyName = 'tenant_id';

    public static string $ownerKeyName = 'owner_id';

    protected array $preparationData = [];

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

    public function preparation(array $preparationData): static
    {
        $this->preparationData = $preparationData;

        if (!$this->table) {
            $this->tableName($preparationData['tableName'] ?? 'custom_blocks');
        }
        return $this;
    }

    public function getPreperationData(): array
    {
        return [
            'multitenancyEnabled' => static::$multitenancyEnabled,
            'tenantKeyName' => static::$tenantKeyName,
            'ownerKeyName' => static::$ownerKeyName,
            ...(is_array($this->preparationData) ? $this->preparationData : []),
        ];
    }

    public function __call($method, $args)
    {
        $this->initilizeDriver();

        return $this->driverInstance->tableName($this->table)->prepare($this->getPreperationData())->{$method}(...$args);
    }

    protected function initilizeDriver()
    {
        $driverClass = $this->driverInstance ? get_class($this->driverInstance) : null;

        $targetClass = $this->driver ?: static::$defaultDriver;

        if (!$driverClass || $driverClass !== $targetClass) {
            if (!class_exists($targetClass)) {
                throw new \RuntimeException("Driver class [$targetClass] does not exist.");
            }

            $this->driverInstance = new $targetClass();
        }
    }
}
