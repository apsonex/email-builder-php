<?php

namespace Apsonex\EmailBuilderPhp\Support\EmailConfigs\EmailConfigDrivers;

use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Contracts\EmailConfigWithAiDriverConract;
use Apsonex\EmailBuilderPhp\Support\EmailConfigs\EmailConfigDrivers\Concerns\Fakeable;

abstract class BaseEmailConfigDriver implements EmailConfigWithAiDriverConract
{
    use Makebale, Fakeable;

    protected bool $valid = false;

    protected ?array $response = null;

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function response(): ?array
    {
        return $this->response;
    }
}
