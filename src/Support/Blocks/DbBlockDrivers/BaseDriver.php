<?php

namespace Apsonex\EmailBuilderPhp\Support\Blocks\DbBlockDrivers;

use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Concerns\InteractsWithDatabase;
use Apsonex\EmailBuilderPhp\Contracts\DbBlockDriverContract;

abstract class BaseDriver implements DbBlockDriverContract
{
    use InteractsWithDatabase, Makebale;
}
