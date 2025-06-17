<?php

use Apsonex\EmailBuilderPhp\Support\CustomBlock\CustomBlock;
use Apsonex\EmailBuilderPhp\Support\CustomBlock\Drivers\SqliteCustomBlockDriver;

beforeEach(function () {
    // Create in-memory SQLite
    $this->pdo = new PDO('sqlite::memory:');
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set up multitenancy and driver
    CustomBlock::enableMultitenancy('tenant_id');
    CustomBlock::ownerKeyName('owner_id');

    $this->blocks = CustomBlock::make()
        ->driver(SqliteCustomBlockDriver::class)
        ->preperation([
            'pdo' => $this->pdo,
        ]);
});

describe('sqlite_custom_block_test', function () {

    it('sqlite_cbd_can_store_a_block_with_tenant_and_owner', function () {
        $block = $this->blocks->store([
            'tenant_id' => 1,
            'owner_id' => 1,
            'data' => [
                'name' => 'Welcome',
                'category' => 'header',
                'html' => '<h1>Hello</h1>',
            ]
        ]);

        expect($block)->toBeArray();
        expect($block['name'])->toBe('Welcome');
        expect($block['html'])->toBe('<h1>Hello</h1>');
    });

    it('sqlite_cbd_can_list_blocks_with_keyword_filter_and_tenant', function () {
        $this->blocks->store([
            'tenant_id' => 1,
            'owner_id' => 1,
            'data' => [
                'name' => 'Welcome Block',
                'category' => 'header',
                'html' => '<h1>Hello</h1>',
            ]
        ]);

        $this->blocks->store([
            'tenant_id' => 'tenant-2',
            'owner_id' => 'user-2',
            'data' => [
                'name' => 'Goodbye Block',
                'category' => 'footer',
                'html' => '<h1>Bye</h1>',
            ]
        ]);

        $results = $this->blocks->index([
            'keyword' => 'Welcome',
            'tenant_id' => 1,
        ]);

        expect($results)->toHaveCount(1);
        expect($results[0]['data']['name'])->toBe('Welcome Block');
    });

    it('sqlite_cbd_can_show_a_specific_block_by_id', function () {
        $block = $this->blocks->store([
            'tenant_id' => 1,
            'owner_id' => 1,
            'data' => [
                'name' => 'Featured',
                'category' => 'promo',
                'html' => '<h1>Promo</h1>',
            ]
        ]);

        $found = $this->blocks->show([
            'id' => $block['id'],
            'owner_id' => 1
        ]);

        expect($found)->not->toBeNull();
        expect($found['data']['name'])->toBe('Featured');
    });

    it('sqlite_cbd_can_update_a_block', function () {
        $block = $this->blocks->store([
            'tenant_id' => 1,
            'owner_id' => 1,
            'data' => [
                'name' => 'Old Name',
                'category' => 'general',
                'html' => '<p>Old</p>',
            ]
        ]);

        $updated = $this->blocks->update([
            'id' => $block['id'],
            'owner_id' => 1,
        ], [
            'tenant_id' => 1,
            'owner_id' => 1,
            'data' => [
                'name' => 'New Name',
                'html' => '<p>Updated</p>',
            ]
        ]);

        expect($updated)->toBe(true);

        $found = $this->blocks->show(['id' => $block['id']]);

        expect($found['id'])->toBe($block['id']);
    });

    it('sqlite_cbd_can_delete_a_block', function () {
        $block = $this->blocks->store([
            'tenant_id' => 1,
            'owner_id' => 1,
            'data' => [
                'name' => 'Temp Block',
                'category' => 'temp',
                'html' => '<div>Temp</div>',
            ]
        ]);

        $deleted = $this->blocks->destroy([
            'id' => $block['id'],
            'owner_id' => 1,
        ]);
        $found = $this->blocks->show([
            'id' => $block['id'],
            'owner_id' => 1
        ]);

        expect($deleted)->toBeTrue();
        expect($found)->toBeNull();
    });
});
