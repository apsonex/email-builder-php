<?php

use Apsonex\EmailBuilderPhp\Support\EmailTemplate;

beforeEach(function () {
    $this->industry = 'accounting';
    $this->category = 'marketing';
    $this->subject = 'exclusive-offer-free-financial-consultation';
});

describe('EmailTemplateTest', function () {
    it('subject_template_returns_expected_data', function () {
        $emailTemplate = EmailTemplate::make();
        $result = $emailTemplate->get($this->industry, $this->category, $this->subject);
        expect($result)->toBeArray();
    });

    it('subject_template_returns_null_for_missing_file', function () {

        $this->subject = 'gibrish';

        $emailTemplate = EmailTemplate::make();
        $result = $emailTemplate->get($this->industry, $this->category, $this->subject);
        expect($result)->toBeNull();
    });
});
