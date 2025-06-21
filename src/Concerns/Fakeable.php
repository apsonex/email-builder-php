<?php

namespace Apsonex\EmailBuilderPhp\Concerns;

trait Fakeable
{
    protected bool $fake = false;
    protected int $fakeType = 200;
    protected int $fakeSpeed = 10;

    public function fake(int $fakeType = 200, int $streamSpeed = 10): static
    {
        $this->fake = true;
        $this->fakeType = $fakeType;
        $this->fakeSpeed = $streamSpeed;
        return $this;
    }

    protected static function availableFakeTypes(): array
    {
        return [200];
    }
}
