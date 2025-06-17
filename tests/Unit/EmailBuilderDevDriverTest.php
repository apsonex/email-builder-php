<?php

use Apsonex\EmailBuilderPhp\Support\EmailConfigs\EmailConfigDrivers\EmailBuilderDevDriver;

describe('email_builder_dev_driver', function () {
    it('returns_fake_response_when_fake_mode_enabled', function () {
        $driver = (new EmailBuilderDevDriver())->fake(200);

        $response = $driver->query([
            'category'         => 'hero',
            'provider'         => 'deepseek',
            'provider_api_key' => 'test-api-key',
            'ai_model'         => 'deepseek-chat',
            'user_prompt'      => 'Generate a test email block',
            'count'            => 1,
            'timeout'          => 10,
            'token'            => 'test-token',
        ]);

        expect($response)
            ->toBeArray()
            ->toHaveKey('status')
            ->toHaveKey('code')
            ->status->toBe('success')
            ->code->toBe(200);
    });
});
