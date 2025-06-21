<?php

use Illuminate\Support\Collection;
use Apsonex\EmailBuilderPhp\Support\Objects\Industry;
use Apsonex\EmailBuilderPhp\Support\Objects\EmailType;
use Apsonex\EmailBuilderPhp\Support\Prebuilt\PrebuiltEmail;
use Apsonex\EmailBuilderPhp\Support\Objects\EmailConfig\EmailConfig;

beforeEach(function () {
    $this->emails = (new PrebuiltEmail())
        ->endpoint('http://localhost:3099')
        ->token('dummy'); // Only if required by backend
});

describe('prebuilt_email_test', function () {

    it('fetches_all_industries', function () {
        $industries = $this->emails->industries();

        expect($industries)->toBeInstanceOf(Collection::class)
            ->and($industries->first())->toBeInstanceOf(Industry::class);
    });

    it('fetches_types_for_a_given_industry', function () {
        $industrySlug = 'accounting'; // ensure this exists on your server

        $types = $this->emails->typesByIndustry($industrySlug);

        expect($types)->toBeInstanceOf(Collection::class)
            ->and($types->first())->toBeInstanceOf(EmailType::class);
    });

    it('fetches_email_config_blocks_for_an_industry_and_type', function () {
        $industrySlug = 'accounting'; // ensure this exists
        $typeSlug = 'marketing';      // ensure this type exists for the above industry

        $configs = $this->emails->config($industrySlug, $typeSlug);

        expect($configs)->toBeInstanceOf(Collection::class)
            ->and($configs->first())->toBeInstanceOf(EmailConfig::class);
    });
});
