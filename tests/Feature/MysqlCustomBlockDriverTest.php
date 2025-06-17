<?php

use Apsonex\EmailBuilderPhp\Support\Blocks\CustomBlock;
use Apsonex\EmailBuilderPhp\Support\Blocks\CustomBlockDrivers\MysqlCustomBlockDriver;

use function Pest\Laravel\expectException;

beforeEach(function () {
    $this->dbHost = '127.0.0.1';
    $this->dbPort = 3306;
    $this->dbUser = 'root';
    $this->dbPass = 'password';
    $this->dbName = 'email_builder_php_db';

    // Connect to MySQL server (without selecting DB)
    $this->mysqlPdo = new PDO(
        "mysql:host={$this->dbHost};port={$this->dbPort}",
        $this->dbUser,
        $this->dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Create database if not exists
    $this->mysqlPdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->dbName}`");

    // Reconnect with DB selected
    $pdo = new PDO(
        "mysql:host={$this->dbHost};port={$this->dbPort};dbname={$this->dbName}",
        $this->dbUser,
        $this->dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Drop all existing tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE `$table`");
    }

    $this->pdo = $pdo;

    CustomBlock::enableMultitenancy('tenant_id');
    CustomBlock::ownerKeyName('owner_id');

    $this->blocks = CustomBlock::make()
        ->driver(MysqlCustomBlockDriver::class)
        ->preperation([
            'pdo' => $this->pdo
        ]);
});

afterEach(function () {
    // Drop the database after test
    $this->mysqlPdo->exec("DROP DATABASE `$this->dbName`");
});

describe('mysql_custom_block_driver', function () {

    it('can_store_a_block_with_tenant_and_owner', function () {
        $block = $this->blocks->store([
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1',
            'data' => [
                'name' => 'Welcome',
                'category' => 'header',
                'html' => '<h1>Hello</h1>',
            ]
        ]);

        expect($block)->toBeArray();
        expect($block['name'])->toBe('Welcome');
        expect($block['html'])->toBe('<h1>Hello</h1>');
        expect($block['tenant_id'])->toBe('tenant1');
        expect($block['owner_id'])->toBe('owner1');
        expect(isset($block['id']))->toBeTrue();
    });

    it('can_list_blocks_filtered_by_tenant_and_owner', function () {
        $this->blocks->store([
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1',
            'data' => [
                'name' => 'Block A',
                'category' => 'cat1',
                'html' => '<div>A</div>',
            ]
        ]);

        $this->blocks->store([
            'tenant_id' => 'tenant2',
            'owner_id' => 'owner2',
            'data' => [
                'name' => 'Block B',
                'category' => 'cat2',
                'html' => '<div>B</div>',
            ]
        ]);

        $results = $this->blocks->index([
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1',
        ]);

        expect($results)->toHaveCount(1);
        expect($results[0]['name'])->toBe('Block A');
    });

    it('can_show_a_specific_block', function () {
        $block = $this->blocks->store([
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1',
            'data' => [
                'name' => 'Specific Block',
                'category' => 'cat',
                'html' => '<div>Specific</div>',
            ]
        ]);

        $found = $this->blocks->show([
            'id' => $block['id'],
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1',
        ]);

        expect($found)->not()->toBeNull();
        expect($found['name'])->toBe('Specific Block');
    });

    it('returns_null_when_block_not_found', function () {
        $found = $this->blocks->show([
            'id' => 'non-existent-id',
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1',
        ]);

        expect($found)->toBeNull();
    });

    it('can_update_a_block', function () {
        $block = $this->blocks->store([
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1',
            'data' => [
                'name' => 'Old Name',
                'category' => 'oldcat',
                'html' => '<p>Old</p>',
            ]
        ]);

        $updated = $this->blocks->update([
            'id' => $block['id'],
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1',
        ], [
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1',
            'data' => [
                'name' => 'New Name',
                'html' => '<p>New</p>',
            ]
        ]);

        expect($updated)->toBeTrue();

        $found = $this->blocks->show([
            'id' => $block['id'],
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1',
        ]);

        expect($found['name'])->toBe('New Name');
        expect($found['html'])->toBe('<p>New</p>');
    });

    it('can_delete_a_block', function () {
        $block = $this->blocks->store([
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1',
            'data' => [
                'name' => 'Delete Me',
                'category' => 'temp',
                'html' => '<div>Delete</div>',
            ]
        ]);

        $deleted = $this->blocks->destroy([
            'id' => $block['id'],
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1',
        ]);

        $found = $this->blocks->show([
            'id' => $block['id'],
            'tenant_id' => 'tenant1',
            'owner_id' => 'owner1',
        ]);

        expect($deleted)->toBeTrue();
        expect($found)->toBeNull();
    });

    it('throws_exception_if_tenant_id_is_missing_when_multitenancy_is_enabled', function () {
        expect(fn() => $this->blocks->store([
            'owner_id' => 'owner1',
            'data' => ['name' => 'No tenant'],
        ]))->toThrow(RuntimeException::class, 'tenant_id is required');
    });

    it('throws_exception_if_owner_id_is_missing', function () {
        expect(fn() => $this->blocks->store([
            'tenant_id' => 'tenant1',
            'data' => ['name' => 'No owner'],
        ]))->toThrow(RuntimeException::class, 'owner_id is required');
    });
});
