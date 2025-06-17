<?php

use Apsonex\EmailBuilderPhp\Support\Blocks\DbBlock;
use Apsonex\EmailBuilderPhp\Support\EmailConfigs\DbEmailConfigDrivers\MysqlDriver;
use Illuminate\Support\Str;

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

    DbBlock::enableMultitenancy('tenant_id');
    DbBlock::ownerKeyName('owner_id');

    $this->blocks = DbBlock::make()
        ->driver(MysqlDriver::class)
        ->preparation([
            'pdo' => $this->pdo
        ]);
});

describe('mysql_email_block_driver_test', function () {

    it('mysql_email_block_driver_throws_if_no_pdo_instance_passed_in_prepare', function () {
        $driver = new MysqlDriver();
        $driver->prepare([]); // no pdo
    })->throws(RuntimeException::class, 'PDO instance is required for MysqlCustomBlockDriver.');

    it('mysql_email_block_driver_can_store_a_block_and_returns_stored_data', function () {
        $result = $this->blocks->store(sampleEmailConfigData([
            'name' => 'One',
            'config' => ['one' => 'two']
        ]));

        expect($result)->toBeArray()
            ->toHaveKey('id')
            ->toHaveKey('owner_id')
            ->and($result['name'])->toBe('One')
            ->and($result['type'])->toBe('template')
            ->and($result['config'])->toBe(['one' => 'two']);
    });

    it('mysql_email_block_driver_throws_when_required_fields_are_missing_on_store', function () {
        $data = ['name' => 'No owner', 'slug' => 'slug'];
        $this->blocks->store($data);
    })->throws(RuntimeException::class, 'owner_id is required');

    it('mysql_email_block_driver_returns_false_when_insert_fails', function () {
        $this->blocks->store(sampleEmailConfigData(['owner_id' => 1, 'tenant_id' => null]));
    })->throws(RuntimeException::class, 'tenant_id is required');

    it('mysql_email_block_driver_can_show_a_stored_block_by_id', function () {
        $d = $this->blocks->store(sampleEmailConfigData());

        $result = $this->blocks->show(['id' => $d['id'], 'owner_id' => $d['owner_id'], 'tenant_id' => $d['tenant_id']]);

        expect($result)->toBeArray()
            ->toHaveKey('id', 1)
            ->toHaveKey('name', $d['name']);
    });

    it('mysql_email_block_driver_throws_if_required_filters_missing_on_show', function () {
        $this->blocks->show([]);
    })->throws(RuntimeException::class);

    it('mysql_email_block_driver_can_update_a_block_successfully', function () {
        // Insert a record first
        $record = $this->blocks->store(sampleEmailConfigData());

        $filters = ['id' => $record['id'], 'owner_id' => $record['owner_id'], 'tenant_id' => $record['tenant_id']];

        $updated = $this->blocks->update($filters, ['name' => $newName = 'Updated Name']);

        $updatedBlock = $this->blocks->show($filters);

        expect($updated)->toBeTrue();
        expect($updatedBlock['name'])->toBe($newName);
    });

    it('mysql_email_block_driver_returns_false_when_updating_non_existent_block', function () {
        $filters = ['id' => 999, 'owner_id' => 1, 'tenant_id' => 1];
        $result = $this->blocks->update($filters, sampleEmailConfigData());
        expect($result)->toBeFalse();
    });

    it('mysql_email_block_driver_can_destroy_a_block_by_id', function () {
        expect(count($this->blocks->index()) <= 0)->toBeTrue();

        $record = $this->blocks->store(sampleEmailConfigData());

        expect(count($this->blocks->index()) > 0)->toBeTrue();

        $result = $this->blocks->destroy($record);

        expect($result)->toBeTrue();

        expect(count($this->blocks->index()) <= 0)->toBeTrue();
    });

    it('mysql_email_block_driver_throws_on_destroy_if_id_and_uuid_missing', function () {
        $this->blocks->destroy(['owner_id' => 1]);
    })->throws(RuntimeException::class);

    it('mysql_email_block_driver_throws_on_destroy_if_owner_id_missing', function () {
        $this->blocks->destroy(['id' => 1]);
    })->throws(RuntimeException::class);
});
