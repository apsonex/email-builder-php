<?php

use Illuminate\Support\Str;
use Apsonex\EmailBuilderPhp\Support\Blocks\DbBlockDrivers\FileDriver;

function resetTempDir($tempDir, $make = true)
{
    if (is_dir($tempDir)) {
        $it = new RecursiveDirectoryIterator($tempDir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($tempDir);
    }

    if ($make) {
        mkdir($tempDir, 0755, true);
    }
}

beforeEach(function () {
    // Prepare a temporary directory for file storage during tests
    $this->tempDir = sys_get_temp_dir() . '/custom_block_test';

    resetTempDir($this->tempDir, true);

    $this->driver = (new FileDriver())->prepare([
        'storagePath' => $this->tempDir,
        'multitenancyEnabled' => true,
        'tenantKeyName' => 'tenant_id',
        'ownerKeyName' => 'owner_id',
    ]);
});

afterEach(function () {
    resetTempDir($this->tempDir, false);
});

describe('db_block_file_driver_test', function () {

    it('file_db_block_can_store_a_block_with_tenant_and_owner', function () {
        $block = $this->driver->store(sampleData());

        expect($block)->toBeArray();
        expect($block['tenant_id'])->toBe(1);
        expect($block['owner_id'])->toBe(1);
        expect($block['name'])->toBe($block['name']);

        // Check file exists at expected path
        $path = "{$this->tempDir}/tenant_{$block['tenant_id']}/owner_{$block['owner_id']}/{$block['uuid']}.json";
        expect(file_exists($path))->toBeTrue();
    });

    it('file_db_block_can_index_blocks_filtered_by_tenant_and_owner', function () {
        // Store two blocks with different tenants and owners
        $block1 = $this->driver->store(sampleData(['tenant_id' => 1, 'owner_id' => 1]));
        $block2 = $this->driver->store(sampleData(['name' => 'New Name', 'tenant_id' => 2, 'owner_id' => 2]));

        // Filter by tenant1 only
        $results = $this->driver->index([
            'tenant_id' => 1
        ]);

        expect(count($results))->toBe(1);

        expect($results[0]['id'])->toBe($block1['id']);

        // Filter by tenant1 and owner1 only
        $results = $this->driver->index([
            'tenant_id' => 2,
            'owner_id' => 2
        ]);
        expect(count($results))->toBe(1);
        expect($results[0]['id'])->toBe($block2['id']);

        // Filter by keyword match in name
        $results = $this->driver->index([
            'keyword' => 'New Name',
            'tenant_id' => 2,
            'owner_id' => 2,
        ]);
        expect(count($results))->toBe(1);
        expect($results[0]['name'])->toContain('New Name');
    });

    it('file_db_block_can_show_a_specific_block_by_id_owner_and_tenant', function () {
        $block = $this->driver->store(sampleData());

        $found = $this->driver->show([
            'id' => $block['id'],
            'owner_id' => 1,
            'tenant_id' => 1,
        ]);

        expect($found)->not->toBeNull();

        // Wrong tenant returns null
        $notFound = $this->driver->show([
            'id' => $block['id'],
            'owner_id' => 1,
            'tenant_id' => 2,
        ]);
        expect($notFound)->toBeNull();
    });

    it('file_db_block_can_update_a_block', function () {
        $block = $this->driver->store(sampleData());

        $updated = $this->driver->update([
            'id' => $block['id'],
            'owner_id' => 1,
            'tenant_id' => 1,
        ], [
            'name' => 'New Name',
            'category' => 'newCat',
            'config' => ['One' => 'Two']
        ]);

        expect($updated)->toBeTrue();

        $found = $this->driver->show([
            'id' => $block['id'],
            'owner_id' => 1,
            'tenant_id' => 1,
        ]);

        expect($found['name'])->toBe('New Name');
        expect($found['config'])->toBe(['One' => 'Two']);
        expect($found['category'])->toBe('newCat');
    });

    it('file_db_block_can_destroy_a_block', function () {
        $block = $this->driver->store(sampleData());

        $deleted = $this->driver->destroy([
            'id' => $block['id'],
            'owner_id' => 1,
            'tenant_id' => 1,
        ]);

        expect($deleted)->toBeTrue();

        $found = $this->driver->show([
            'id' => $block['id'],
            'owner_id' => 1,
            'tenant_id' => 1,
        ]);

        expect($found)->toBeNull();
    });
});
