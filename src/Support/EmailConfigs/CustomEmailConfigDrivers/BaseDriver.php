<?php
namespace Apsonex\EmailBuilderPhp\Support\EmailConfigs\CustomEmailConfigDrivers;

use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Concerns\InteractsWithDatabase;

abstract class BaseDriver
{
    use Makebale, InteractsWithDatabase;

}
