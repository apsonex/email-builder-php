<?php

namespace Apsonex\EmailBuilderPhp\Support\Blocks\DbBlockDrivers;

use Illuminate\Support\Str;

class FileDriver extends BaseDriver
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

        $pattern = $this->multitenancyEnabled
            ? ($ownerId ? "/tenant_{$tenantId}/owner_{$ownerId}/*.json" : '/tenant_*/owner_*/' . '*.json')
            : ($ownerId ? "/owner_{$ownerId}/*.json" : '/owner_*/' . '*.json');

        $files = glob($this->storagePath . $pattern);

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
        $id = Str::uuid()->toString();
        $name = $input['name'] ?? null;
        $category = $input['category'] ?? 'uncategorized';
        $ownerId = $input[$this->ownerKey] ?? null;
        $tenantId = $input[$this->tenantKey] ?? null;
        $config = $input['config'] ?? [];

        if (!$name || !$ownerId || ($this->multitenancyEnabled && !$tenantId)) {
            return false;
        }

        $block = [
            'id' => $id,
            'uuid' => $id,
            'name' => $name,
            'slug' => $input['slug'] ?? '',
            'description' => $input['description'] ?? '',
            'preview' => $input['preview'] ?? '',
            'type' => $input['type'] ?? 'block',
            'category' => $category,
            'config' => $config,
            $this->ownerKey => $ownerId,
        ];

        if ($this->multitenancyEnabled) {
            $block[$this->tenantKey] = $tenantId;
        }

        // Determine file path
        $filePath = $this->getFilePath($ownerId, $id, $tenantId);

        // Ensure directory exists
        @mkdir(dirname($filePath), 0777, true);

        // Save block to JSON file
        $saved = file_put_contents($filePath, json_encode($block, JSON_PRETTY_PRINT));

        return $saved !== false ? $block : false;
    }

    public function update(array $whereClause, array $input): bool
    {
        $existing = $this->show($whereClause);
        if (!$existing) return false;

        $existing['name'] = $input['name'] ?? $existing['name'];
        $existing['slug'] = $input['slug'] ?? $existing['slug'];
        $existing['description'] = $input['description'] ?? $existing['description'];
        $existing['preview'] = $input['preview'] ?? $existing['preview'];
        $existing['type'] = $input['type'] ?? $existing['type'];
        $existing['category'] = $input['category'] ?? $existing['category'];
        $existing['config'] = array_key_exists('config', $input) ? $input['config'] : $existing['config'];

        $filePath = $this->getFilePath($existing[$this->ownerKey], $existing['id'], $this->multitenancyEnabled ? $existing[$this->tenantKey] : null);

        $saved = file_put_contents($filePath, json_encode($existing, JSON_PRETTY_PRINT));

        return $saved !== false;
    }

    public function destroy(array $whereClause): bool
    {
        $existing = $this->show($whereClause);
        if (!$existing) return false;

        $filePath = $this->getFilePath($existing[$this->ownerKey], $existing['id'], $this->multitenancyEnabled ? $existing[$this->tenantKey] : null);

        return file_exists($filePath) ? unlink($filePath) : false;
    }
}
