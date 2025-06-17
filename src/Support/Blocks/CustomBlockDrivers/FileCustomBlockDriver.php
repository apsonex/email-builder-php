<?php

namespace Apsonex\EmailBuilderPhp\Support\Blocks\CustomBlockDrivers;

use Illuminate\Support\Str;
use Apsonex\EmailBuilderPhp\Contracts\CustomBlockContract;

class FileCustomBlockDriver extends BaseDriver implements CustomBlockContract
{
    protected string $storagePath;

    public function prepare(array $args): static
    {
        $this->multitenancyEnabled = $args['multitenancyEnabled'] ?? false;
        $this->tenantKey = $args['tenantKeyName'] ?? 'tenant_id';
        $this->ownerKey = $args['ownerKeyName'] ?? 'owner_id';

        $this->storagePath = rtrim($args['storagePath'] ?? sys_get_temp_dir() . '/custom_blocks', '/');

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
                // scan only that tenant/owner directory
                $dir = $this->getDirPath($ownerId, $tenantId);
                if (!is_dir($dir)) return [];
                $files = glob($dir . '/*.json');
            } elseif ($tenantId) {
                // scan all owners under tenant directory
                $tenantDir = "{$this->storagePath}/tenant_{$tenantId}";
                if (!is_dir($tenantDir)) return [];
                $files = glob($tenantDir . '/owner_*/' . '*.json');
            } else {
                // scan all tenants > all owners > all files
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

            if (!empty($filters['category']) && ($content['category'] ?? '') !== $filters['category']) {
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

        // Validate tenant and owner match
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
        $data = $input['data'] ?? [];

        $ownerId = $input[$this->ownerKey] ?? $data[$this->ownerKey] ?? null;
        if (!$ownerId) {
            return false;
        }

        $id = $data['id'] ?? Str::uuid()->toString();

        $tenantId = null;
        if ($this->multitenancyEnabled) {
            $tenantId = $input[$this->tenantKey] ?? $data[$this->tenantKey] ?? null;
            if (!$tenantId) {
                return false;
            }
        }

        $dir = $this->getDirPath($ownerId, $tenantId);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $block = [
            'id' => $id,
            'name' => $data['name'] ?? '',
            'category' => $data['category'] ?? '',
            'html' => $data['html'] ?? '',
            $this->ownerKey => $ownerId,
        ];

        if ($this->multitenancyEnabled) {
            $block[$this->tenantKey] = $tenantId;
        }

        $block['data'] = $data;

        $filePath = $this->getFilePath($ownerId, $id, $tenantId);

        $saved = file_put_contents($filePath, json_encode($block, JSON_PRETTY_PRINT));

        if ($saved === false) {
            return false;
        }

        return $block;
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
        if (!file_exists($filePath)) {
            return false;
        }

        $existing = json_decode(file_get_contents($filePath), true);
        if (!$existing) {
            return false;
        }

        $data = array_merge($existing['data'] ?? [], $input['data'] ?? []);

        $existing['name'] = $data['name'] ?? $existing['name'];
        $existing['category'] = $data['category'] ?? $existing['category'];
        $existing['html'] = $data['html'] ?? $existing['html'];
        $existing['data'] = $data;

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
