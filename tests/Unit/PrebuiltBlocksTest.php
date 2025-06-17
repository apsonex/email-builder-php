<?php
// tests/Support/Blocks/PrebuiltTest.php

use Apsonex\EmailBuilderPhp\Support\Blocks\Prebuilt;

beforeEach(function () {
    $this->prebuilt = new Prebuilt();
});

describe('prebuilt_blocks_test', function () {

    it('prebuilt_blocks_returns_categories_array', function () {
        $data = $this->prebuilt->categories();

        expect($data)->toBeArray()
            ->and($data)->toHaveKey('categories')
            ->and($data['categories'])->toBeArray()
            ->and($data['categories'][0])->toHaveKeys(['label', 'slug', 'description']);
    });

    it('prebuilt_blocks_returns_blocks_by_category', function () {
        $data = $this->prebuilt->blocksByCategory('hero');

        expect($data)->not()->toBeNull()
            ->and($data)->toHaveKey('category', 'hero')
            ->and($data)->toHaveKey('items')
            ->and($data['items'])->toBeArray()
            ->and($data['items'][0])->toHaveKey('name')
            ->and($data['items'][0])->toHaveKey('type')
            ->and($data['items'][0])->toHaveKey('description');
    });
});
