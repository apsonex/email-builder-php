<?php

use Apsonex\EmailBuilderPhp\Support\Industries;

describe('industries_test', function () {
    it('returns_a_slug_label_array_of_industries', function () {
        $industries = Industries::make();

        expect($industries->all())->toBeArray();
    });

    it('returns_the_industry_data_if_file_exists', function () {
        $industries = Industries::make();
        $data = $industries->industry('non-profit');

        expect($data)->toBeArray();
    });

    it('returns_null_if_industry_file_does_not_exist', function () {
        $industries = Industries::make();
        $data = $industries->industry('some zibbrish');

        expect($data)->toBeNull();
    });
});
