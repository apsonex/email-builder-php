<?php

namespace Apsonex\EmailBuilderPhp\Contracts;

interface CustomBlockContract
{
    public function tableName(string $tableName): static;

    public function prepare(array $args): static;

    public function tenantKeyName(): ?string;

    public function ownerKeyName(): ?string;

    public function multitenancyEnabled(): bool;

    public function index(array $filters = []): array;

    public function show(array $filters): ?array;

    public function store(array $data): array|bool;

    public function update(array $whereClauses, array $data): bool;

    public function destroy(array $whereClauses): bool;
}
