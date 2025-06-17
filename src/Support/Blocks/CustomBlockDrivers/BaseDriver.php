<?php

namespace Apsonex\EmailBuilderPhp\Support\Blocks\CustomBlockDrivers;

abstract class BaseDriver
{
    protected string $table = 'custom_blocks';

    protected bool $multitenancyEnabled = false;
    protected string $tenantKey = 'tenant_id';
    protected string $ownerKey = 'owner_id';

    public function tenantKeyName(): ?string
    {
        return $this->multitenancyEnabled ? $this->tenantKey : null;
    }

    public function ownerKeyName(): ?string
    {
        return $this->ownerKey;
    }

    public function multitenancyEnabled(): bool
    {
        return $this->multitenancyEnabled;
    }

    public function enableMultitenancy(string $tenantKey = 'tenant_id', string $ownerKey = 'owner_id'): static
    {
        $this->multitenancyEnabled = true;
        $this->tenantKey = $tenantKey;
        $this->ownerKey = $ownerKey;

        return $this;
    }
}
