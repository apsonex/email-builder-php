<?php

namespace Apsonex\EmailBuilderPhp\Support\Prebuilt;

use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Concerns\HasHttpClient;

abstract class BasePrebuilt
{
    use Makebale, HasHttpClient;
}
