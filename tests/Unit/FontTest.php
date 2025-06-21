<?php

use Apsonex\EmailBuilderPhp\Support\Font;
use Apsonex\EmailBuilderPhp\Support\Objects\Font\FontPaginationResponse;
use Apsonex\EmailBuilderPhp\Support\Objects\Font\FontObject;

beforeEach(function () {
    $this->font = (new Font())
        ->endpoint('http://localhost:3099')
        ->token('dummy'); // if auth is not enforced
});

describe('font_test', function () {

    it('fetches_paginated_fonts_list', function () {
        $response = $this->font->index(page: 1, limit: 2);

        expect($response)->toBeInstanceOf(FontPaginationResponse::class)
            ->and($response->fonts)->toBeArray()
            ->and($response->fonts[0])->toBeInstanceOf(FontObject::class);
    });

    it('searches_fonts_by_query', function () {
        $response = $this->font->search('ab', 1, 2);

        expect($response)->toBeInstanceOf(FontPaginationResponse::class)
            ->and($response->fonts)->not()->toBeEmpty()
            ->and($response->fonts[0])->toBeInstanceOf(FontObject::class);
    });

    it('fetches_fonts_by_keys', function () {
        $fonts = $this->font->byKeys(['abel', 'abhaya-libre'], 2);

        expect($fonts)->toHaveCount(2)
            ->and($fonts->first())->toBeInstanceOf(FontObject::class);
    });

    it('fetches_font_by_family', function () {
        $font = $this->font->byFamily('Abhaya Libre');

        expect($font)->toBeInstanceOf(FontObject::class)
            ->and($font->family)->toBe('Abhaya Libre');
    });

    it('fetches_fonts_by_multiple_families', function () {
        $fonts = $this->font->byFamilies(['Abel', 'Abhaya Libre'], 2);

        expect($fonts)->toHaveCount(2)
            ->and($fonts->first())->toBeInstanceOf(FontObject::class);
    });

    it('fetches_fonts_by_type_category_with_pagination', function () {
        $response = $this->font->byType('sans-serif', 1, 2);

        expect($response)->toBeInstanceOf(FontPaginationResponse::class)
            ->and($response->fonts)->not()->toBeEmpty()
            ->and($response->fonts[0])->toBeInstanceOf(FontObject::class);
    });
});
