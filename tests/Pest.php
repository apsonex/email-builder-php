<?php

use Tests\TestCase;

uses(TestCase::class)->in(__DIR__);


function sampleEmailConfigPayload()
{
    return [
        'category'         => 'hero',
        'provider'         => 'deepseek',
        'provider_api_key' => 'test-api-key',
        'ai_model'         => 'deepseek-chat',
        'user_prompt'      => 'Generate a test email block',
        'count'            => 1,
        'timeout'          => 10,
        'token'            => env('EMAIL_BUILDER_AUTH_TOKEN'),
    ];
}
