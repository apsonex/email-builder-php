<?php

namespace Apsonex\EmailBuilderPhp\Support\EmailConfigs\EmailConfigDrivers\Concerns;

trait Fakeable
{
    protected bool $fake = false;
    protected int $fakeType = 200;

    public function fake(int $fakeType = 200): static
    {
        $this->fake = true;
        $this->fakeType = in_array($fakeType, static::availableFakeTypes()) ? $fakeType : 200;
        return $this;
    }

    protected static function availableFakeTypes(): array
    {
        return [200];
    }
}
