<?php

use Illuminate\Support\Arr;
use Apsonex\EmailBuilderPhp\Support\Blocks\DbBlock;
use Apsonex\EmailBuilderPhp\Support\Blocks\DbBlockDrivers\SqliteDriver;

beforeEach(function () {
    // Create in-memory SQLite
    $this->pdo = new PDO('sqlite::memory:');
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set up multitenancy and driver
    DbBlock::enableMultitenancy('tenant_id');
    DbBlock::ownerKeyName('owner_id');

    $this->blocks = DbBlock::make()
        ->driver(SqliteDriver::class)
        ->preparation([
            'pdo' => $this->pdo,
        ]);
});

describe('db_block_sqlite_driver_test', function () {

    it('sqlite_db_block_can_store_a_block_with_tenant_and_owner', function () {
        $block = $this->blocks->store(sampleData(['id' => 1]));

        expect($block['id'])->toBe(1);
    });

    it('sqlite_db_block_can_list_blocks_with_keyword_filter_and_tenant', function () {
        $this->blocks->store(sampleData(['name' => 'One']));

        $this->blocks->store(sampleData(['name' => 'Two']));

        $results = $this->blocks->index([
            'keyword' => 'One',
            'owner_id' => 1,
            'tenant_id' => 1,
        ]);

        expect($results)->toHaveCount(1);
        expect($results[0]['name'])->toBe('One');
    });

    it('sqlite_db_block_can_show_a_specific_block_by_id', function () {
        $block = $this->blocks->store(sampleData(['name' => 'Two']));

        $found = $this->blocks->show(Arr::only(sampleData(['name' => 'Two']), ['id', 'owner_id', 'tenant_id']));

        expect($found)->not->toBeNull();
        expect($found['name'])->toBe('Two');
    });

    it('sqlite_db_block_can_update_a_block', function () {
        $block = $this->blocks->store(sampleData());

        $updated = $this->blocks->update([
            'id' => $block['id'],
            'owner_id' => 1,
            'tenant_id' => 1,
        ], ['name' => 'New Name']);

        expect($updated)->toBe(true);

        $found = $this->blocks->show(['id' => $block['id']]);

        expect($found['name'])->toBe('New Name');
    });

    it('sqlite_db_block_can_delete_a_block', function () {
        $block = $this->blocks->store(sampleData());

        $deleted = $this->blocks->destroy([
            'id' => $block['id'],
            'owner_id' => 1,
            'tenant_id' => 1,
        ]);
        $found = $this->blocks->show([
            'id' => $block['id'],
            'owner_id' => 1,
            'tenant_id' => 1,
        ]);

        expect($deleted)->toBeTrue();
        expect($found)->toBeNull();
    });
});
