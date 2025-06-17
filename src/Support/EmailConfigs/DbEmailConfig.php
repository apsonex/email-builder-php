<?php

namespace Apsonex\EmailBuilderPhp\Support\EmailConfigs;

use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Concerns\HasTableName;
use Apsonex\EmailBuilderPhp\Contracts\DbEmailConfigDriverContract;
use Apsonex\EmailBuilderPhp\Support\BaseDb;
use Apsonex\EmailBuilderPhp\Support\EmailConfigs\DbEmailConfigDrivers\SqliteDriver;

/**
 * @method array index(array $filters = [])
 * @method ?array show(array $filters)
 * @method array|bool store(array $data)
 * @method bool update(array $whereClauses, array $data)
 * @method bool destroy(array $whereClauses)
 */
class DbEmailConfig extends BaseDb
{
    static string $defaultTableName = 'email_configurations';

    public static string $defaultDriver = SqliteDriver::class;
}
