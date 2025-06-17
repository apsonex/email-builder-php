<?php

namespace Apsonex\EmailBuilderPhp\Support\Blocks\DbBlockDrivers;

use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Contracts\DbDriverContract;
use Apsonex\EmailBuilderPhp\Concerns\InteractsWithDatabase;

abstract class BaseDriver implements DbDriverContract
{
    use InteractsWithDatabase, Makebale;
}
