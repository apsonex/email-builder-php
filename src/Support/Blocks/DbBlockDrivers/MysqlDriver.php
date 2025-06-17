<?php

namespace Apsonex\EmailBuilderPhp\Support\Blocks\DbBlockDrivers;

use \PDO;
use \RuntimeException;
use Illuminate\Support\Str;

class MysqlDriver extends BaseDriver
{
    protected ?PDO $pdo = null;

    public function prepare(array $options): static
    {
        if (empty($options['pdo']) || !($options['pdo'] instanceof PDO)) {
            throw new RuntimeException('PDO instance is required for MysqlCustomBlockDriver.');
        }
        $this->pdo = $options['pdo'];

        // You can also override multitenancy & keys here if needed
        if (isset($options['multitenancyEnabled'])) {
            $this->multitenancyEnabled = (bool) $options['multitenancyEnabled'];
        }
        if (!empty($options['tenantKeyName'])) {
            $this->tenantKey = $options['tenantKeyName'];
        }
        if (!empty($options['ownerKeyName'])) {
            $this->ownerKey = $options['ownerKeyName'];
        }

        // Create table if not exists
        $this->createTableIfNotExists();

        return $this;
    }

    protected function createTableIfNotExists(): void
    {
        $columns = [
            "id VARCHAR(36) PRIMARY KEY",
            "name VARCHAR(255) NOT NULL",
            "owner_id VARCHAR(255) NULL",
            "category VARCHAR(255) NULL",
            "html TEXT NULL",
            "data TEXT NOT NULL",
            "created_at DATETIME DEFAULT CURRENT_TIMESTAMP",
            "updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
        ];

        if ($this->multitenancyEnabled) {
            $columns[] = "{$this->tenantKey} VARCHAR(255) NULL";
        }

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (" . implode(',', $columns) . ")";

        $this->pdo->exec($sql);
    }


    public function index(array $filters = []): array
    {
        $params = [];
        $where = [];

        if ($this->multitenancyEnabled && !empty($filters[$this->tenantKey])) {
            $where[] = "{$this->tenantKey} = :tenant_id";
            $params[':tenant_id'] = $filters[$this->tenantKey];
        }

        if (!empty($filters[$this->ownerKey])) {
            $where[] = "{$this->ownerKey} = :owner_id";
            $params[':owner_id'] = $filters[$this->ownerKey];
        }

        if (!empty($filters['keyword'])) {
            $where[] = "(name LIKE :keyword OR category LIKE :keyword)";
            $params[':keyword'] = '%' . $filters['keyword'] . '%';
        }

        $sql = "SELECT * FROM custom_blocks";
        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->rowToData($row), $rows);
    }

    public function store(array $data): array|bool
    {
        if (empty($data[$this->ownerKey])) {
            throw new RuntimeException("{$this->ownerKey} is required");
        }

        if ($this->multitenancyEnabled && empty($data[$this->tenantKey])) {
            throw new RuntimeException("{$this->tenantKey} is required");
        }

        $id = $data['id'] ?? $this->generateUuid();

        $name = $data['data']['name'] ?? null;
        $category = $data['data']['category'] ?? null;
        $html = $data['data']['html'] ?? null;
        $jsonData = json_encode($data['data'] ?? []);

        // Define columns and placeholders
        $columns = ['id', 'owner_id', 'name', 'category', 'html', 'data'];
        $placeholders = [':id', ':owner_id', ':name', ':category', ':html', ':data'];
        $bindings = [
            ':id' => $id,
            ':owner_id' => $data[$this->ownerKey],
            ':name' => $name,
            ':category' => $category,
            ':html' => $html,
            ':data' => $jsonData,
        ];

        // Add tenant column if multitenancy is enabled
        if ($this->multitenancyEnabled) {
            $columns[] = $this->tenantKey;
            $placeholders[] = ':tenant_id';
            $bindings[':tenant_id'] = $data[$this->tenantKey];
        }

        // Build and execute SQL
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);

        return array_merge($data['data'], [
            'id' => $id,
            $this->ownerKey => $data[$this->ownerKey],
            $this->tenantKey => $this->multitenancyEnabled ? $data[$this->tenantKey] : null,
        ]);
    }


    public function show(array $filters): ?array
    {
        if (empty($filters['id'])) {
            throw new RuntimeException("id is required");
        }

        if (empty($filters[$this->ownerKey])) {
            throw new RuntimeException("{$this->ownerKey} is required");
        }

        if ($this->multitenancyEnabled && empty($filters[$this->tenantKey])) {
            throw new RuntimeException("{$this->tenantKey} is required");
        }

        // Build SQL
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND {$this->ownerKey} = :owner_id";

        $params = [
            ':id' => $filters['id'],
            ':owner_id' => $filters[$this->ownerKey],
        ];

        if ($this->multitenancyEnabled) {
            $sql .= " AND {$this->tenantKey} = :tenant_id";
            $params[':tenant_id'] = $filters[$this->tenantKey];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->rowToData($row) : null;
    }


    public function update(array $filters, array $data): bool
    {
        if (empty($filters['id'])) {
            throw new RuntimeException("id is required");
        }

        if (empty($filters[$this->ownerKey])) {
            throw new RuntimeException("{$this->ownerKey} is required");
        }

        if ($this->multitenancyEnabled && empty($filters[$this->tenantKey])) {
            throw new RuntimeException("{$this->tenantKey} is required");
        }

        // Fetch the existing record
        $existing = $this->show($filters);

        if (!$existing) {
            return false;
        }

        $name = $data['data']['name'] ?? $existing['name'] ?? null;
        $category = $data['data']['category'] ?? $existing['category'] ?? null;
        $html = $data['data']['html'] ?? $existing['html'] ?? null;
        $jsonData = json_encode($data['data'] ?? $existing['data'] ?? []);

        $sql = "UPDATE {$this->table}
            SET name = :name, category = :category, html = :html, data = :data
            WHERE id = :id AND {$this->ownerKey} = :owner_id";

        $params = [
            ':name' => $name,
            ':category' => $category,
            ':html' => $html,
            ':data' => $jsonData,
            ':id' => $filters['id'],
            ':owner_id' => $filters[$this->ownerKey],
        ];

        if ($this->multitenancyEnabled) {
            $sql .= " AND {$this->tenantKey} = :tenant_id";
            $params[':tenant_id'] = $filters[$this->tenantKey];
        }

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }


    public function destroy(array $filters): bool
    {
        if (empty($filters['id'])) {
            throw new RuntimeException("id is required");
        }

        if (empty($filters[$this->ownerKey])) {
            throw new RuntimeException("{$this->ownerKey} is required");
        }

        if ($this->multitenancyEnabled && empty($filters[$this->tenantKey])) {
            throw new RuntimeException("{$this->tenantKey} is required");
        }

        $sql = "DELETE FROM {$this->table}
            WHERE id = :id AND {$this->ownerKey} = :owner_id";

        $params = [
            ':id' => $filters['id'],
            ':owner_id' => $filters[$this->ownerKey],
        ];

        if ($this->multitenancyEnabled) {
            $sql .= " AND {$this->tenantKey} = :tenant_id";
            $params[':tenant_id'] = $filters[$this->tenantKey];
        }

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }


    protected function rowToData(array $row): array
    {
        $data = json_decode($row['data'] ?? '{}', true);

        if (!is_array($data)) {
            $data = [];
        }

        return array_merge($data, [
            'id' => $row['id'] ?? null,
            $this->tenantKey => $row[$this->tenantKey] ?? null,
            $this->ownerKey => $row[$this->ownerKey] ?? null,
            'name' => $row['name'] ?? null,
            'category' => $row['category'] ?? null,
            'html' => $row['html'] ?? null,
        ]);
    }


    protected function generateUuid(): string
    {
        return Str::uuid();
    }
}
