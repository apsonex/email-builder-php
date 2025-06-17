<?php

namespace Apsonex\EmailBuilderPhp\Support\EmailConfigs\DbEmailConfigDrivers;

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

        if (isset($options['multitenancyEnabled'])) {
            $this->multitenancyEnabled = (bool) $options['multitenancyEnabled'];
        }
        if (!empty($options['tenantKeyName'])) {
            $this->tenantKey = $options['tenantKeyName'];
        }
        if (!empty($options['ownerKeyName'])) {
            $this->ownerKey = $options['ownerKeyName'];
        }

        $this->createTableIfNotExists();

        return $this;
    }

    protected function createTableIfNotExists(): void
    {
        $columns = [
            "id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY",
            "uuid VARCHAR(36) NOT NULL",
            "name VARCHAR(255) NOT NULL",
            "type VARCHAR(255) NOT NULL",
            "category VARCHAR(255) NOT NULL",
            "industry VARCHAR(255) NOT NULL",
            "description TEXT NULL",
            "preview TEXT NULL",
            "{$this->ownerKey} BIGINT UNSIGNED NULL",
            "config JSON NOT NULL",
            "created_at DATETIME DEFAULT CURRENT_TIMESTAMP",
            "updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
        ];

        if ($this->multitenancyEnabled) {
            $columns[] = "{$this->tenantKey} BIGINT UNSIGNED NULL";
        }

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (" . implode(", ", $columns) . ")";
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

        $sql = "SELECT * FROM {$this->table}";

        if (!empty($where)) {
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
        // Required checks
        if (empty($data[$this->ownerKey])) {
            throw new RuntimeException("{$this->ownerKey} is required");
        }

        if ($this->multitenancyEnabled && empty($data[$this->tenantKey])) {
            throw new RuntimeException("{$this->tenantKey} is required");
        }

        if (empty($data['name'])) {
            throw new RuntimeException("name is required");
        }

        if (empty($data['industry'])) {
            throw new RuntimeException("industry is required");
        }

        if (empty($data['category'])) {
            throw new RuntimeException("category is required");
        }

        $name = $data['name'];
        $type = 'template';
        $preview = $data['preview'] ?? null;
        $category = $data['category'] ?? 'uncategorized';
        $industry = $data['industry'];
        $description = $data['description'] ?? null;
        $jsonConfig = json_encode($data['config'] ?? []);
        $uuid = $this->generateUuid();

        $columns = [$this->ownerKey, 'name', 'category', 'description', 'type', 'industry', 'config', 'uuid', 'preview'];
        $placeholders = [':owner_id', ':name', ':category', ':description', ':type', ':industry', ':config', ':uuid', ':preview'];
        $bindings = [
            ':owner_id' => $data[$this->ownerKey],
            ':name' => $name,
            ':category' => $category,
            ':description' => $description,
            ':type' => $type,
            ':industry' => $industry,
            ':config' => $jsonConfig,
            ':uuid' => $uuid,
            ':preview' => $preview,
        ];

        if ($this->multitenancyEnabled) {
            $columns[] = $this->tenantKey;
            $placeholders[] = ':tenant_id';
            $bindings[':tenant_id'] = $data[$this->tenantKey];
        }

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute($bindings);

        if (!$success) {
            return false;
        }

        // Get the auto-incremented id generated by the DB
        $id = $this->pdo->lastInsertId();

        // Return the stored data merged with id, ownerKey and tenantKey (if applicable)
        return array_merge($data, [
            'id' => (int)$id,
            $this->ownerKey => $data[$this->ownerKey],
            $this->tenantKey => $this->multitenancyEnabled ? $data[$this->tenantKey] : null,
            'uuid' => $uuid,
        ]);
    }


    public function show(array $filters): ?array
    {
        if (empty($filters[$this->ownerKey])) {
            throw new RuntimeException("{$this->ownerKey} is required");
        }

        if (empty($filters['id']) && empty($filters['uuid'])) {
            throw new RuntimeException("Either id or uuid is required");
        }

        if ($this->multitenancyEnabled && empty($filters[$this->tenantKey])) {
            throw new RuntimeException("{$this->tenantKey} is required");
        }

        $sql = "SELECT * FROM `{$this->table}` WHERE ";

        $params = [
            ':owner' => $filters[$this->ownerKey],
        ];

        // Build WHERE condition for id and/or uuid
        $idCondition = '';
        if (!empty($filters['id'])) {
            $idCondition = "`id` = :id";
            $params[':id'] = $filters['id'];
        }

        $uuidCondition = '';
        if (!empty($filters['uuid'])) {
            $uuidCondition = "`uuid` = :uuid";
            $params[':uuid'] = $filters['uuid'];
        }

        // Combine id and uuid with OR if both present
        if ($idCondition && $uuidCondition) {
            $sql .= "($idCondition OR $uuidCondition)";
        } elseif ($idCondition) {
            $sql .= $idCondition;
        } else {
            $sql .= $uuidCondition;
        }

        // Owner check
        $sql .= " AND `{$this->ownerKey}` = :owner";

        // Tenant check if enabled
        if ($this->multitenancyEnabled) {
            $sql .= " AND `{$this->tenantKey}` = :tenant";
            $params[':tenant'] = $filters[$this->tenantKey];
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

        $existing = $this->show($filters);

        if (!$existing) {
            return false;
        }

        // Prepare updated fields, fallback to existing if not provided
        $name = $data['name'] ?? $existing['name'];
        $industry = $data['industry'] ?? $existing['industry'];
        $category = $data['category'] ?? $existing['category'];
        $preview = $data['preview'] ?? $existing['preview'];
        $description = $data['description'] ?? $existing['description'];

        // Handle config: if present, encode as JSON, else keep existing
        if (isset($data['config'])) {
            $config = json_encode($data['config'], true);
        } else {
            $config = is_array($existing['config']) ? json_encode($existing['config'], true) : $existing['config'];
        }

        $sql = "UPDATE {$this->table} SET
        name = :name,
        industry = :industry,
        category = :category,
        preview = :preview,
        description = :description,
        config = :config,
        updated_at = CURRENT_TIMESTAMP
        WHERE id = :id AND {$this->ownerKey} = :owner_id";

        $params = [
            ':name' => $name,
            ':industry' => $industry,
            ':category' => $category,
            ':preview' => $preview,
            ':description' => $description,
            ':config' => $config,
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
        if (empty($filters['id']) && empty($filters['uuid'])) {
            throw new RuntimeException("Either id or uuid is required");
        }

        if (empty($filters[$this->ownerKey])) {
            throw new RuntimeException("{$this->ownerKey} is required");
        }

        if ($this->multitenancyEnabled && empty($filters[$this->tenantKey])) {
            throw new RuntimeException("{$this->tenantKey} is required");
        }

        $sql = "DELETE FROM {$this->table} WHERE {$this->ownerKey} = :owner_id";
        $params = [
            ':owner_id' => $filters[$this->ownerKey],
        ];

        if ($this->multitenancyEnabled) {
            $sql .= " AND {$this->tenantKey} = :tenant_id";
            $params[':tenant_id'] = $filters[$this->tenantKey];
        }

        if (!empty($filters['id'])) {
            $sql .= " AND id = :id";
            $params[':id'] = $filters['id'];
        } elseif (!empty($filters['uuid'])) {
            $sql .= " AND uuid = :uuid";
            $params[':uuid'] = $filters['uuid'];
        }

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }


    protected function rowToData(array $row): array
    {
        return [
            'id' => $row['id'] ?? null,
            $this->tenantKey => $row[$this->tenantKey] ?? null,
            $this->ownerKey => $row[$this->ownerKey] ?? null,
            'name' => $row['name'] ?? null,
            'category' => $row['category'] ?? null,
            'industry' => $row['industry'] ?? null,
            'type' => $row['type'] ?? null,
            'description' => $row['description'] ?? null,
            'preview' => $row['preview'] ?? null,
            'config' => isset($row['config'])
                ? (is_string($row['config']) ? json_decode($row['config'], true) : $row['config'])
                : [],
        ];
    }


    protected function generateUuid(): string
    {
        return (string) Str::uuid();
    }
}
