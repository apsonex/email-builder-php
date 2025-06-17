<?php

use Illuminate\Support\Str;
use Apsonex\EmailBuilderPhp\Support\EmailConfigs\DbEmailConfigDrivers\FileDriver;

function resetDir($dir)
{
    if (is_dir($dir)) {
        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }

        rmdir($dir);
    }

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

beforeEach(function () {
    $this->tempDir = sys_get_temp_dir() . '/email_config_test';

    resetDir($this->tempDir);

    $this->driver = (new FileDriver())->prepare([
        'storagePath' => $this->tempDir,
        'multitenancyEnabled' => true,
        'tenantKeyName' => 'tenant_id',
        'ownerKeyName' => 'owner_id',
    ]);
});

afterEach(function () {
    $it = new RecursiveDirectoryIterator($this->tempDir, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

    foreach ($files as $file) {
        $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
    }

    if (is_dir($this->tempDir)) {
        rmdir($this->tempDir);
    }
});

describe('email_config_file_driver_test', function () {

    it('can_store_a_config_with_tenant_and_owner', function () {
        $config = $this->driver->store([
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1',
            'data' => [
                'key' => 'mail_from_name',
                'value' => 'Gurinder',
            ]
        ]);

        dd($config);

        expect($config)->toBeArray();
        expect($config['tenant_id'])->toBe('tenant1');
        expect($config['owner_id'])->toBe('owner1');
        expect($config['data']['key'])->toBe('mail_from_name');
        expect($config['data']['value'])->toBe('Gurinder');

        $path = "{$this->tempDir}/tenant_tenant1/owner_owner1/{$config['id']}.json";
        expect(file_exists($path))->toBeTrue();
    });

    it('can_index_configs_by_tenant_and_owner', function () {
        $config1 = $this->driver->store([
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1',
            'data' => [
                'key' => 'site_name',
                'value' => 'Alpha Site',
            ]
        ]);
        $config2 = $this->driver->store([
            'tenant_id' => 'tenant2',
            'owner_id' => 'owner2',
            'data' => [
                'key' => 'site_name',
                'value' => 'Beta Site',
            ]
        ]);

        $results = $this->driver->index([
            'tenant_id' => 'tenant1',
        ]);

        dd($results);

        expect(count($results))->toBe(1);
        expect($results[0]['id'])->toBe($config1['id']);

        $results = $this->driver->index([
            'tenant_id' => 'tenant2',
            'owner_id' => 'owner2',
        ]);
        expect(count($results))->toBe(1);
        expect($results[0]['id'])->toBe($config2['id']);
    });

    it('can_show_a_config_by_id_owner_tenant', function () {
        $config = $this->driver->store([
            'tenant_id' => 'tenantX',
            'owner_id' => 'ownerX',
            'data' => [
                'key' => 'theme',
                'value' => 'dark',
            ]
        ]);

        $found = $this->driver->show([
            'id' => $config['id'],
            'owner_id' => 'ownerX',
            'tenant_id' => 'tenantX',
        ]);

        expect($found)->not->toBeNull();
        expect($found['data']['key'])->toBe('theme');

        $notFound = $this->driver->show([
            'id' => $config['id'],
            'owner_id' => 'ownerX',
            'tenant_id' => 'wrong',
        ]);
        expect($notFound)->toBeNull();
    });

    it('can_update_a_config', function () {
        $config = $this->driver->store([
            'tenant_id' => 'tenantU',
            'owner_id' => 'ownerU',
            'data' => [
                'key' => 'footer_text',
                'value' => 'Old Footer',
            ]
        ]);

        $updated = $this->driver->update([
            'id' => $config['id'],
            'owner_id' => 'ownerU',
            'tenant_id' => 'tenantU',
        ], [
            'data' => [
                'value' => 'New Footer',
            ]
        ]);

        expect($updated)->toBeTrue();

        $found = $this->driver->show([
            'id' => $config['id'],
            'owner_id' => 'ownerU',
            'tenant_id' => 'tenantU',
        ]);

        expect($found['data']['value'])->toBe('New Footer');
        expect($found['data']['key'])->toBe('footer_text'); // unchanged
    });

    it('can_destroy_a_config', function () {
        $config = $this->driver->store([
            'tenant_id' => 'tenantD',
            'owner_id' => 'ownerD',
            'data' => [
                'key' => 'delete_me',
                'value' => 'gone',
            ]
        ]);

        $deleted = $this->driver->destroy([
            'id' => $config['id'],
            'owner_id' => 'ownerD',
            'tenant_id' => 'tenantD',
        ]);

        expect($deleted)->toBeTrue();

        $found = $this->driver->show([
            'id' => $config['id'],
            'owner_id' => 'ownerD',
            'tenant_id' => 'tenantD',
        ]);

        expect($found)->toBeNull();
    });
});
