<?php

use Tests\TestCase;
use Illuminate\Contracts\Support\Arrayable;
use Apsonex\EmailBuilderPhp\Support\AiPayload;
use Apsonex\EmailBuilderPhp\Support\EmailConfigs\Payload as EmailPayload;
use Apsonex\EmailBuilderPhp\Support\Blocks\Payload as BlockPayload;

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

function samepleBusinessinfo(): AiPayload\BusinessInfo
{
    return AiPayload\BusinessInfo::make()
        ->industry('accounting')
        ->businessType('Certified CPA in Canada')
        ->logo('https://placehold.co/200x80.png?text=Fake+Logo')
        ->primaryBrandColor('#123456')
        ->primaryAltBrandColor('#ffffff')
        ->secondaryBrandColor('#654321')
        ->secondaryAltBrandColor('#f4f4f4')
        ->name('Acme Accounting')
        ->address('42 Fictional Blvd, Faketown, NY 12345, USA')
        ->phone('+1 (555) 123-4567')
        ->email('hello@acme.fake')
        ->website('https://acme.fake')
        ->googleMapLink('https://www.google.com/maps/search/acme+accounting/@43.6534426,-79.3840957,17z')
        ->privacyPolicy('https://acme.fake/privacy')
        ->termsOfUse('https://acme.fake/terms')
        ->addSocialLink('facebook', 'https://facebook.com/acmeaccounting')
        ->addSocialLink('linkedin', 'https://linkedin.com/company/acmeaccounting')
        ->addSocialLink('twitter', 'https://twitter.com/acmeaccounting');
}

function sampleAiEmailConfigPayload(): Arrayable
{
    return EmailPayload\EmailBuilderDev::make(
        apiKey: AiPayload\ApiKey::make(apiKey: env('EMAIL_BUILDER_AUTH_TOKEN'), orgId: null, projectId: null),
        provider: AiPayload\Provider::make()->deepseek(),
        businessInfo: samepleBusinessinfo(),
        subject: 'Your Tax Documents Are Ready for Review',
        tone: 'Friendly',
        prompt: null,
        maxTokens: 8000,
        maxSteps: 10,
        stockImagesProviderApiKeys: AiPayload\StockImagesProviderApiKey::make(),
    );
}

function sampleAiBlockConfigPayload(): Arrayable
{
    return BlockPayload\EmailBuilderDev::make(
        apiKey: AiPayload\ApiKey::make(apiKey: env('EMAIL_BUILDER_AUTH_TOKEN'), orgId: null, projectId: null),
        provider: AiPayload\Provider::make()->deepseek(),
        businessInfo: samepleBusinessinfo(),
        category: 'hero',
        tone: 'Friendly',
        count: 2,
        prompt: null,
        maxTokens: 8000,
        maxSteps: 10,
        stockImagesProviderApiKeys: AiPayload\StockImagesProviderApiKey::make(),
    );
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
