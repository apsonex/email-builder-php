<?php

use Apsonex\EmailBuilderPhp\Support\Asset;

it('fetches_asset_manifest_successfully_from_local_server', function () {
    Asset::$endpoint = 'http://localhost:3099';

    $result = Asset::manifest();

    expect($result)->toBeArray()
        ->and($result['status'])->toBe('success')
        ->and($result['manifest'])->toBeArray()
        ->and(array_keys($result['manifest']))->not()->toBeEmpty();
});
