<?php
namespace Apsonex\EmailBuilderPhp\Support\EmailConfigs\DbEmailConfigDrivers;

use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Contracts\DbDriverContract;
use Apsonex\EmailBuilderPhp\Concerns\InteractsWithDatabase;

abstract class BaseDriver implements DbDriverContract
{
    use Makebale, InteractsWithDatabase;

}
