<?php

use Apsonex\EmailBuilderPhp\Support\Objects\Blocks\Block;
use Apsonex\EmailBuilderPhp\Support\Objects\Blocks\BlockCategory;
use Illuminate\Support\Collection;
use Apsonex\EmailBuilderPhp\Support\Prebuilt\PrebuiltBlock;

beforeEach(function () {
    $this->blocks = (new PrebuiltBlock())
        ->endpoint('http://localhost:3099')
        ->token('dummy'); // if auth is required
});

describe('prebuilt_block_test', function () {

    it('fetches_all_block_categories', function () {
        $categories = $this->blocks->categories();

        expect($categories)->toBeInstanceOf(Collection::class)
            ->and($categories->first())->toBeInstanceOf(BlockCategory::class);
    });

    it('fetches_blocks_by_category', function () {
        $categorySlug = 'hero'; // ensure this category exists in mock

        $blocks = $this->blocks->category($categorySlug);

        expect($blocks)->toBeInstanceOf(Collection::class)
            ->and($blocks->first())->toBeInstanceOf(Block::class);
    });
});
