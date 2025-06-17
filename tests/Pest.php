<?php

use Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

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

function sampleBlockData($merge = [])
{
    return [
        'name' => 'Name',
        'slug' => 'slug',
        'description' => 'desc',
        'preview' => 'preview',
        'owner_id' => 1,
        'tenant_id' => 1,
        'category' => 'cat',
        'config' => [],
        ...$merge,
    ];
}

function sampleEmailConfigData($merge = [])
{
    return [
        'name' => 'Name',
        'industry' => 'accounting',
        'category' => 'marketing',
        'description' => 'description',
        'preview' => 'preview',
        'type' => 'template',
        'owner_id' => 1,
        'tenant_id' => 1,
        'config' => [],
        ...$merge,
    ];
}
