<?php

namespace Apsonex\EmailBuilderPhp\Support\EmailConfigs\DbEmailConfigDrivers;

use Illuminate\Support\Str;

class SqliteDriver extends BaseDriver
{
    protected \PDO $pdo;

    protected bool $tableExist = false;

    public function prepare(array $args): static
    {
        $this->multitenancyEnabled = $args['multitenancyEnabled'] ?? false;
        $this->tenantKey = $args['tenantKeyName'];
        $this->ownerKey = $args['ownerKeyName'];

        $pdo = $args['pdo'] ?? null;

        if (empty($pdo) || !$pdo instanceof \PDO) {
            throw new \InvalidArgumentException('A valid PDO instance must be provided.');
        }

        $this->pdo = $pdo;

        if (!$this->tableExist) $this->prepareTable();

        return $this;
    }

    protected function prepareTable(): void
    {
        $tenantColumn = $this->multitenancyEnabled
            ? ", {$this->tenantKey} INTEGER"
            : "";

        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS {$this->table} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uuid TEXT, -- uuid
            name TEXT, -- block name
            type TEXT, -- type: default to `template`
            category TEXT, -- block unique slug
            industry TEXT, -- block unique slug
            description TEXT, -- block description
            preview TEXT, -- block preview image path
            config TEXT, -- array encoded as string
            {$this->ownerKey} INTEGER
            {$tenantColumn}
        )
        SQL;

        $this->pdo->exec($sql);

        $this->tableExist = true;
    }

    public function index(array $filters = []): array
    {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($filters['uuid'])) {
            $query .= " AND uuid LIKE :uuid";
            $params[':uuid'] = '%' . $filters['uuid'] . '%';
        }

        if (!empty($filters['name'])) {
            $query .= " AND name LIKE :name";
            $params[':name'] = '%' . $filters['name'] . '%';
        }

        if (!empty($filters['keyword'])) {
            // Assuming you want to search in name and description for keyword
            $query .= " AND (name LIKE :keyword OR description LIKE :keyword)";
            $params[':keyword'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['category'])) {
            $query .= " AND category = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['industry'])) {
            $query .= " AND industry = :industry";
            $params[':industry'] = $filters['industry'];
        }

        if ($this->multitenancyEnabled && !empty($filters[$this->tenantKey])) {
            $query .= " AND {$this->tenantKey} = :tenant";
            $params[':tenant'] = $filters[$this->tenantKey];
        }

        if (!empty($filters[$this->ownerKey])) {
            $query .= " AND {$this->ownerKey} = :owner";
            $params[':owner'] = $filters[$this->ownerKey];
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            // Decode config JSON string into 'config' key, not 'data' (your table has 'config' column)
            $row['config'] = json_decode($row['config'], true);
            $results[] = $row;
        }

        return $results;
    }


    public function show(array $filters): ?array
    {
        [$whereClause, $params] = $this->prepareWhereClauses($filters);

        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$whereClause} LIMIT 1");
        $stmt->execute($params);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        // Decode JSON config column
        $row['config'] = json_decode($row['config'], true);

        return $row;
    }


    public function store(array $input): array|bool
    {
        $uuid = Str::uuid()->toString();
        $name = $input['name'] ?? null;
        $category = $input['category'] ?? null;
        $industry = $input['industry'] ?? null;
        $type = 'template';
        $description = $input['description'] ?? null;
        $ownerId = (int)($input[$this->ownerKey] ?? 0);
        $tenantId = (int)($input[$this->ownerKey] ?? 0);
        $config = $input['config'] ?? [];

        if (!$name || ($ownerId <= 0) || ($this->multitenancyEnabled && $tenantId <= 0) || !$industry || !$category) {
            // Name is required, fail early
            return false;
        }

        $columns = ['uuid', 'name', 'type', 'category', 'industry', 'config', 'description', $this->ownerKey];
        $placeholders = [':uuid', ':name', ':type', ':category', ':industry', ':config', ':description', ':owner'];

        $values = [
            ':uuid' => $uuid,
            ':name' => (string)$name,
            ':type' => (string)$type,
            ':category' => (string)$category,
            ':industry' => (string)$industry,
            ':description' => (string)$description,
            ':config' => json_encode($config),
            ':owner' => (int)$ownerId,
        ];

        if ($this->multitenancyEnabled) {
            $columns[] = $this->tenantKey;
            $placeholders[] = ':tenant';
            $values[':tenant'] = $tenantId;
        }

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute($values);

        if (!$success) {
            return false;
        }

        // Fetch the last inserted ID if using AUTOINCREMENT id
        $lastId = $this->pdo->lastInsertId();

        // Return a consistent array with stored data (including id and decoded config)
        return [
            'id' => (int)$lastId,
            'uuid' => $uuid,
            'name' => $name,
            'type' => $type,
            'industry' => $industry,
            'description' => $description,
            'category' => $category,
            'config' => $config,
            $this->ownerKey => $input[$this->ownerKey] ?? null,
            ...($this->multitenancyEnabled ? [
                $this->tenantKey => $input[$this->tenantKey]
            ] : []),
        ];
    }

    public function update(array $whereClause, array $input): bool
    {
        $existing = $this->show($whereClause);

        if (!$existing) {
            return false;
        }

        // Determine config value: overwrite only if present in input
        $config = array_key_exists('config', $input)
            ? json_encode($input['config'])
            : $existing['config'];

        // Updateable fields (never update id, owner, or tenant keys)
        $fields = [
            'name = :name',
            'industry = :industry',
            'description = :description',
            'preview = :preview',
            'category = :category',
            'config = :config',
        ];

        $values = [
            ':name' => $input['name'] ?? $existing['name'],
            ':industry' => $input['industry'] ?? $existing['industry'],
            ':description' => $input['description'] ?? $existing['description'] ?? null,
            ':preview' => $input['preview'] ?? $existing['preview'] ?? null,
            ':category' => $input['category'] ?? $existing['category'] ?? '',
            ':config' => is_array($config) ? json_encode($config, true) : $config,
        ];

        // Base WHERE clause (id)
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $whereValues = [':id' => $whereClause['id']];

        // Tenant scope
        if ($this->multitenancyEnabled && isset($whereClause[$this->tenantKey])) {
            $sql .= " AND {$this->tenantKey} = :tenant";
            $whereValues[':tenant'] = $whereClause[$this->tenantKey];
        }

        // Owner scope
        if (!empty($this->ownerKey) && isset($whereClause[$this->ownerKey])) {
            $sql .= " AND {$this->ownerKey} = :owner";
            $whereValues[':owner'] = $whereClause[$this->ownerKey];
        }

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array_merge($values, $whereValues));
    }

    public function destroy(array $whereClause): bool
    {
        [$whereClause, $params] = $this->prepareWhereClauses($whereClause);

        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$whereClause}");
        return $stmt->execute($params);
    }

    protected function prepareWhereClauses($whereClause): array
    {
        $conditions = [];
        $params = [];

        foreach ($whereClause as $key => $value) {
            $conditions[] = "{$key} = :{$key}";
            $params[":{$key}"] = $value;
        }

        return [
            implode(' AND ', $conditions),
            $params,
        ];
    }
}
