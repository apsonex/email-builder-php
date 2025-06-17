<?php

use Illuminate\Support\Str;
use Apsonex\EmailBuilderPhp\Support\Blocks\DbBlockDrivers\FileDriver;

beforeEach(function () {
    // Prepare a temporary directory for file storage during tests
    $this->tempDir = sys_get_temp_dir() . '/custom_block_test_' . Str::random(6);
    if (!is_dir($this->tempDir)) {
        mkdir($this->tempDir, 0755, true);
    }

    $this->driver = (new FileDriver())->prepare([
        'storagePath' => $this->tempDir,
        'multitenancyEnabled' => true,
        'tenantKeyName' => 'tenant_id',
        'ownerKeyName' => 'owner_id',
    ]);
});

afterEach(function () {
    // Cleanup temporary test directory recursively
    $it = new RecursiveDirectoryIterator($this->tempDir, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        if ($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }
    if (is_dir($this->tempDir)) {
        rmdir($this->tempDir);
    }
});

describe('db_block_file_driver_test', function () {

    it('file_cbd_can_store_a_block_with_tenant_and_owner', function () {
        $block = $this->driver->store([
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1',
            'data' => [
                'name' => 'Test Block',
                'category' => 'test',
                'html' => '<p>Content</p>',
            ]
        ]);

        expect($block)->toBeArray();
        expect($block['tenant_id'])->toBe('tenant1');
        expect($block['owner_id'])->toBe('owner1');
        expect($block['name'])->toBe('Test Block');
        expect($block['html'])->toBe('<p>Content</p>');

        // Check file exists at expected path
        $path = "{$this->tempDir}/tenant_tenant1/owner_owner1/{$block['id']}.json";
        expect(file_exists($path))->toBeTrue();
    });

    it('file_cbd_can_index_blocks_filtered_by_tenant_and_owner', function () {
        // Store two blocks with different tenants and owners
        $block1 = $this->driver->store([
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1',
            'data' => [
                'name' => 'Alpha Block',
                'category' => 'cat1',
                'html' => '<p>Alpha</p>',
            ]
        ]);
        $block2 = $this->driver->store([
            'tenant_id' => 'tenant2',
            'owner_id' => 'owner2',
            'data' => [
                'name' => 'Beta Block',
                'category' => 'cat2',
                'html' => '<p>Beta</p>',
            ]
        ]);

        // Filter by tenant1 only
        $results = $this->driver->index([
            'tenant_id' => 'tenant1'
        ]);
        expect(count($results))->toBe(1);
        expect($results[0]['id'])->toBe($block1['id']);

        // Filter by tenant1 and owner1 only
        $results = $this->driver->index([
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1'
        ]);
        expect(count($results))->toBe(1);
        expect($results[0]['id'])->toBe($block1['id']);

        // Filter by keyword match in name
        $results = $this->driver->index([
            'keyword' => 'Beta',
            'tenant_id' => 'tenant2'
        ]);
        expect(count($results))->toBe(1);
        expect($results[0]['name'])->toContain('Beta');
    });

    it('file_cbd_can_show_a_specific_block_by_id_owner_and_tenant', function () {
        $block = $this->driver->store([
            'tenant_id' => 'tenantX',
            'owner_id' => 'ownerX',
            'data' => [
                'name' => 'Show Block',
                'category' => 'catX',
                'html' => '<p>Show Content</p>',
            ]
        ]);

        $found = $this->driver->show([
            'id' => $block['id'],
            'owner_id' => 'ownerX',
            'tenant_id' => 'tenantX',
        ]);

        expect($found)->not->toBeNull();
        expect($found['name'])->toBe('Show Block');

        // Wrong tenant returns null
        $notFound = $this->driver->show([
            'id' => $block['id'],
            'owner_id' => 'ownerX',
            'tenant_id' => 'wrongTenant',
        ]);
        expect($notFound)->toBeNull();
    });

    it('file_cbd_can_update_a_block', function () {
        $block = $this->driver->store([
            'tenant_id' => 'tenantU',
            'owner_id' => 'ownerU',
            'data' => [
                'name' => 'Old Name',
                'category' => 'oldcat',
                'html' => '<p>Old HTML</p>',
            ]
        ]);

        $updated = $this->driver->update([
            'id' => $block['id'],
            'owner_id' => 'ownerU',
            'tenant_id' => 'tenantU',
        ], [
            'tenant_id' => 'tenantU',
            'owner_id' => 'ownerU',
            'data' => [
                'name' => 'New Name',
                'html' => '<p>New HTML</p>',
            ]
        ]);

        expect($updated)->toBeTrue();

        $found = $this->driver->show([
            'id' => $block['id'],
            'owner_id' => 'ownerU',
            'tenant_id' => 'tenantU',
        ]);

        expect($found['name'])->toBe('New Name');
        expect($found['html'])->toBe('<p>New HTML</p>');
        expect($found['category'])->toBe('oldcat'); // unchanged
    });

    it('file_cbd_can_destroy_a_block', function () {
        $block = $this->driver->store([
            'tenant_id' => 'tenantD',
            'owner_id' => 'ownerD',
            'data' => [
                'name' => 'Delete Me',
                'category' => 'temp',
                'html' => '<p>Delete content</p>',
            ]
        ]);

        $deleted = $this->driver->destroy([
            'id' => $block['id'],
            'owner_id' => 'ownerD',
            'tenant_id' => 'tenantD',
        ]);

        expect($deleted)->toBeTrue();

        $found = $this->driver->show([
            'id' => $block['id'],
            'owner_id' => 'ownerD',
            'tenant_id' => 'tenantD',
        ]);

        expect($found)->toBeNull();
    });
});
