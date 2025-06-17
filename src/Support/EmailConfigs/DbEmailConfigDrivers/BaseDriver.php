<?php
namespace Apsonex\EmailBuilderPhp\Support\EmailConfigs\DbEmailConfigDrivers;

use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Concerns\InteractsWithDatabase;

abstract class BaseDriver
{
    use Makebale, InteractsWithDatabase;

}
