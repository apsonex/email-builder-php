<?php

namespace Apsonex\EmailBuilderPhp\Support\Blocks\CustomBlockDrivers;

use Illuminate\Support\Str;
use Apsonex\EmailBuilderPhp\Contracts\CustomBlockContract;

class SqliteCustomBlockDriver extends BaseDriver implements CustomBlockContract
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
            ? ", {$this->tenantKey} TEXT"
            : "";

        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS {$this->table} (
            id TEXT PRIMARY KEY,
            name TEXT,
            category TEXT,
            html TEXT,
            data TEXT,
            {$this->ownerKey} TEXT
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

        if (!empty($filters['keyword'])) {
            $query .= " AND name LIKE :keyword";
            $params[':keyword'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['category'])) {
            $query .= " AND category = :category";
            $params[':category'] = $filters['category'];
        }

        if ($this->multitenancyEnabled && !empty($filters[$this->tenantKey])) {
            $query .= " AND {$this->tenantKey} = :tenant";
            $params[':tenant'] = $filters[$this->tenantKey];
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['data'] = json_decode($row['data'], true);
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

        if (!$row) return null;

        $row['data'] = json_decode($row['data'], true);

        return $row;
    }

    public function store(array $input): array|bool
    {
        $id = Str::uuid()->toString();
        $data = $input['data'] ?? [];

        $columns = ['id', 'name', 'category', 'html', 'data', $this->ownerKey];
        $placeholders = [':id', ':name', ':category', ':html', ':data', ':owner'];

        $values = [
            ':id' => $id,
            ':name' => $data['name'] ?? '',
            ':category' => $data['category'] ?? '',
            ':html' => $data['html'] ?? '',
            ':data' => json_encode($data),
            ':owner' => $input[$this->ownerKey] ?? null,
        ];

        if ($this->multitenancyEnabled) {
            $columns[] = $this->tenantKey;
            $placeholders[] = ':tenant';
            $values[':tenant'] = $input[$this->tenantKey] ?? null;
        }

        $sql = sprintf(
            "INSERT INTO {$this->table} (%s) VALUES (%s)",
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);

        return array_merge(['id' => $id], $data);
    }

    public function update(array $whereClause, array $input): bool
    {
        $existing = $this->show($whereClause);

        if (!$existing) return false;

        $data = array_merge($existing['data'], $input['data'] ?? []);

        $fields = [
            'name = :name',
            'category = :category',
            'html = :html',
            'data = :data',
            "{$this->ownerKey} = :owner"
        ];

        $values = [
            ':name' => $data['name'] ?? '',
            ':category' => $data['category'] ?? '',
            ':html' => $data['html'] ?? '',
            ':data' => json_encode($data),
            ':owner' => $input[$this->ownerKey] ?? null,
            ':id' => $whereClause['id'],
        ];

        if ($this->multitenancyEnabled) {
            $fields[] = "{$this->tenantKey} = :tenant";
            $values[':tenant'] = $input[$this->tenantKey] ?? null;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);

        return true;
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
