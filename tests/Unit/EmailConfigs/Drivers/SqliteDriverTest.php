<?php

use Apsonex\EmailBuilderPhp\Support\EmailConfigs\DbEmailConfig;
use Apsonex\EmailBuilderPhp\Support\EmailConfigs\DbEmailConfigDrivers\SqliteDriver;

beforeEach(function () {
    // Create in-memory SQLite connection
    $this->pdo = new PDO('sqlite::memory:');
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set up multitenancy and driver
    DbEmailConfig::enableMultitenancy('tenant_id');
    DbEmailConfig::ownerKeyName('owner_id');

    $this->configs = DbEmailConfig::make()
        ->driver(SqliteDriver::class)
        ->preparation([
            'pdo' => $this->pdo,
        ]);
});

describe('db_email_config_sqlite_driver_test', function () {

    it('sqlite_config_can_store_with_tenant_and_owner', function () {
        $config = $this->configs->store([
            'tenant_id' => 1,
            'owner_id' => 1,
            'config' => [
                'name' => 'SendGrid Config',
                'provider' => 'sendgrid',
                'key' => 'SG.xxxxx',
            ]
        ]);

        expect($config)->toBeArray();
        expect($config['name'])->toBe('SendGrid Config');
        expect($config['provider'])->toBe('sendgrid');
    });

    it('sqlite_config_can_list_with_keyword_and_tenant_filter', function () {
        $this->configs->store([
            'tenant_id' => 1,
            'owner_id' => 1,
            'config' => [
                'name' => 'SendGrid Prod',
                'provider' => 'sendgrid',
            ]
        ]);

        $this->configs->store([
            'tenant_id' => 2,
            'owner_id' => 2,
            'config' => [
                'name' => 'Mailgun Backup',
                'provider' => 'mailgun',
            ]
        ]);

        $results = $this->configs->index([
            'keyword' => 'SendGrid',
            'tenant_id' => 1,
        ]);

        expect($results)->toHaveCount(1);
        expect($results[0]['name'])->toBe('SendGrid Prod');
    });

    it('sqlite_config_can_show_config_by_id', function () {
        $config = $this->configs->store([
            'tenant_id' => 1,
            'owner_id' => 1,
            'config' => [
                'name' => 'Primary SMTP',
                'provider' => 'smtp',
                'host' => 'smtp.mail.com',
            ]
        ]);

        $found = $this->configs->show([
            'id' => $config['id'],
            'owner_id' => 1
        ]);

        expect($found)->not->toBeNull();
        expect($found['config']['provider'])->toBe('smtp');
    });

    it('sqlite_config_can_update_existing_config', function () {
        $config = $this->configs->store([
            'tenant_id' => 1,
            'owner_id' => 1,
            'config' => [
                'name' => 'Old Config',
                'provider' => 'smtp',
            ]
        ]);

        $updated = $this->configs->update([
            'id' => $config['id'],
            'owner_id' => 1
        ], [
            'tenant_id' => 1,
            'owner_id' => 1,
            'config' => [
                'name' => 'Updated Config',
                'host' => 'smtp.updated.com',
            ]
        ]);

        expect($updated)->toBeTrue();

        $found = $this->configs->show(['id' => $config['id']]);
        expect($found['config']['name'])->toBe('Updated Config');
        expect($found['config']['host'])->toBe('smtp.updated.com');
    });

    it('sqlite_config_can_delete_config', function () {
        $config = $this->configs->store([
            'tenant_id' => 1,
            'owner_id' => 1,
            'config' => [
                'name' => 'Temp SMTP',
                'provider' => 'smtp',
            ]
        ]);

        $deleted = $this->configs->destroy([
            'id' => $config['id'],
            'owner_id' => 1,
        ]);

        $found = $this->configs->show([
            'id' => $config['id'],
            'owner_id' => 1
        ]);

        expect($deleted)->toBeTrue();
        expect($found)->toBeNull();
    });
});
