<?php

namespace Apsonex\EmailBuilderPhp\Concerns;

trait InteractsWithDatabase
{
    protected bool $multitenancyEnabled = false;

    protected string $tenantKey = 'tenant_id';

    protected string $ownerKey = 'owner_id';

    protected string $table;

    public function tableName(string $tableName): static
    {
        $this->table = $tableName;
        return $this;
    }

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
