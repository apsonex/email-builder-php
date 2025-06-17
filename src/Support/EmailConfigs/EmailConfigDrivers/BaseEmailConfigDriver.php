<?php

namespace Apsonex\EmailBuilderPhp\Support\EmailConfigs\EmailConfigDrivers;

abstract class BaseEmailConfigDriver
{

    abstract public function query(array $payload, array $headers = []): array;
}
