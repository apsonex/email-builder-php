<?php

namespace Apsonex\EmailBuilderPhp\Support\EmailConfigs\DbEmailConfigDrivers;

use Illuminate\Support\Str;

class FileDriver extends BaseDriver
{
    protected string $storagePath;

    public function prepare(array $args): static
    {
        $this->multitenancyEnabled = $args['multitenancyEnabled'] ?? false;
        $this->tenantKey = $args['tenantKeyName'] ?? 'tenant_id';
        $this->ownerKey = $args['ownerKeyName'] ?? 'owner_id';

        $this->storagePath = rtrim($args['storagePath'] ?? sys_get_temp_dir() . '/email_configs', '/');

        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }

        return $this;
    }

    protected function getDirPath(string $ownerId, ?string $tenantId = null): string
    {
        if ($this->multitenancyEnabled && $tenantId) {
            return "{$this->storagePath}/tenant_{$tenantId}/owner_{$ownerId}";
        }

        return "{$this->storagePath}/owner_{$ownerId}";
    }

    protected function getFilePath(string $ownerId, string $id, ?string $tenantId = null): string
    {
        return $this->getDirPath($ownerId, $tenantId) . "/{$id}.json";
    }

    public function index(array $filters = []): array
    {
        $results = [];

        $tenantId = $filters[$this->tenantKey] ?? null;
        $ownerId = $filters[$this->ownerKey] ?? null;

        if ($this->multitenancyEnabled) {
            if ($ownerId && $tenantId) {
                $dir = $this->getDirPath($ownerId, $tenantId);
                if (!is_dir($dir)) return [];
                $files = glob($dir . '/*.json');
            } elseif ($tenantId) {
                $tenantDir = "{$this->storagePath}/tenant_{$tenantId}";
                if (!is_dir($tenantDir)) return [];
                $files = glob($tenantDir . '/owner_*/' . '*.json');
            } else {
                $files = glob($this->storagePath . '/tenant_*/owner_*/' . '*.json');
            }
        } else {
            if ($ownerId) {
                $dir = $this->getDirPath($ownerId);
                if (!is_dir($dir)) return [];
                $files = glob($dir . '/*.json');
            } else {
                $files = glob($this->storagePath . '/owner_*/' . '*.json');
            }
        }

        foreach ($files as $file) {
            $content = json_decode(file_get_contents($file), true);
            if (!$content) continue;

            if (!empty($filters['keyword']) && stripos($content['name'] ?? '', $filters['keyword']) === false) {
                continue;
            }

            if ($this->multitenancyEnabled && $tenantId && ($content[$this->tenantKey] ?? null) != $tenantId) {
                continue;
            }

            if ($ownerId && ($content[$this->ownerKey] ?? null) != $ownerId) {
                continue;
            }

            $results[] = $content;
        }

        return $results;
    }

    public function show(array $filters): ?array
    {
        $id = $filters['id'] ?? null;
        $ownerId = $filters[$this->ownerKey] ?? null;
        $tenantId = $filters[$this->tenantKey] ?? null;

        if (!$id || !$ownerId || ($this->multitenancyEnabled && !$tenantId)) {
            return null;
        }

        $filePath = $this->getFilePath($ownerId, $id, $tenantId);

        if (!file_exists($filePath)) return null;

        $content = json_decode(file_get_contents($filePath), true);

        if ($this->multitenancyEnabled && ($content[$this->tenantKey] ?? null) != $tenantId) {
            return null;
        }

        if (($content[$this->ownerKey] ?? null) != $ownerId) {
            return null;
        }

        return $content;
    }

    public function store(array $input): array|bool
    {
        $config = $input['data'] ?? [];

        $ownerId = $input[$this->ownerKey] ?? $config[$this->ownerKey] ?? null;
        if (!$ownerId) {
            return false;
        }

        $id = $config['id'] ?? Str::uuid()->toString();

        $tenantId = null;
        if ($this->multitenancyEnabled) {
            $tenantId = $input[$this->tenantKey] ?? $config[$this->tenantKey] ?? null;
            if (!$tenantId) {
                return false;
            }
        }

        $dir = $this->getDirPath($ownerId, $tenantId);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $data = [
            'id' => $id,
            'name' => $config['name'] ?? '',
            'provider' => $config['provider'] ?? '',
            'config' => $config,
            $this->ownerKey => $ownerId,
        ];

        if ($this->multitenancyEnabled) {
            $data[$this->tenantKey] = $tenantId;
        }

        $filePath = $this->getFilePath($ownerId, $id, $tenantId);
        $saved = file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));

        if ($saved === false) {
            return false;
        }

        return $data;
    }

    public function update(array $whereClause, array $input): bool
    {
        $id = $whereClause['id'] ?? null;
        $ownerId = $whereClause[$this->ownerKey] ?? null;
        $tenantId = $whereClause[$this->tenantKey] ?? null;

        if (!$id || !$ownerId || ($this->multitenancyEnabled && !$tenantId)) {
            return false;
        }

        $filePath = $this->getFilePath($ownerId, $id, $tenantId);
        if (!file_exists($filePath)) return false;

        $existing = json_decode(file_get_contents($filePath), true);
        if (!$existing) return false;

        $updatedConfig = array_merge($existing['config'] ?? [], $input['config'] ?? []);

        $existing['name'] = $updatedConfig['name'] ?? $existing['name'];
        $existing['provider'] = $updatedConfig['provider'] ?? $existing['provider'];
        $existing['config'] = $updatedConfig;

        $existing[$this->ownerKey] = $ownerId;
        if ($this->multitenancyEnabled) {
            $existing[$this->tenantKey] = $tenantId;
        }

        $saved = file_put_contents($filePath, json_encode($existing, JSON_PRETTY_PRINT));

        return $saved !== false;
    }

    public function destroy(array $whereClause): bool
    {
        $id = $whereClause['id'] ?? null;
        $ownerId = $whereClause[$this->ownerKey] ?? null;
        $tenantId = $whereClause[$this->tenantKey] ?? null;

        if (!$id || !$ownerId || ($this->multitenancyEnabled && !$tenantId)) {
            return false;
        }

        $filePath = $this->getFilePath($ownerId, $id, $tenantId);

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }
}
