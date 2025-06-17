<?php
namespace Apsonex\EmailBuilderPhp\Support\EmailConfigs\DbEmailConfigDrivers;

use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Concerns\InteractsWithDatabase;
use Apsonex\EmailBuilderPhp\Contracts\DbEmailConfigDriverContract;

abstract class BaseDriver implements DbEmailConfigDriverContract
{
    use Makebale, InteractsWithDatabase;

}
