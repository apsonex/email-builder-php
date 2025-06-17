<?php

use Apsonex\EmailBuilderPhp\Support\EmailConfigs\EmailConfigDrivers\EmailConfigEmailBuilderDevDriver;

describe('block_email_builder_dev_driver_test', function () {
    it('returns_fake_response_when_fake_mode_enabled', function () {

        $driver = (new EmailConfigEmailBuilderDevDriver())->fake(200);

        $response = $driver->query(sampleEmailConfigPayload());

        expect($response->response())
            ->toBeArray()
            ->toHaveKey('status')
            ->toHaveKey('code')
            ->status->toBe('success')
            ->code->toBe(200);
    });
});
