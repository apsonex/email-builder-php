<?php

namespace Apsonex\EmailBuilderPhp\Support\Blocks;

use Apsonex\EmailBuilderPhp\Support\BaseDb;
use Apsonex\EmailBuilderPhp\Support\Blocks\DbBlockDrivers\{FileDriver};

/**
 * @method array index(array $filters = [])
 * @method ?array show(string $id)
 * @method array|bool store(array $data)
 * @method bool update(string $id, array $data)
 * @method bool destroy(string $id)
 */
class DbBlock extends BaseDb
{
    static string $defaultTableName = 'custom_blocks';

    public static string $defaultDriver = FileDriver::class;
}
