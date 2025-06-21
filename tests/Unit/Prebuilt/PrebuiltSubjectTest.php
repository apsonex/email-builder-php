<?php

use Illuminate\Support\Collection;
use Apsonex\EmailBuilderPhp\Support\Prebuilt\PrebuiltSubject;
use Apsonex\EmailBuilderPhp\Support\Objects\Industry;
use Apsonex\EmailBuilderPhp\Support\Objects\Subject\SubjectIndustry;

beforeEach(function () {
    $this->subject = (new PrebuiltSubject())
        ->endpoint('http://localhost:3099')
        ->token('dummy'); // If token is not required, you can omit this
});

describe('prebuilt_subject_tests', function () {

    it('fetches_industries_for_subjects', function () {
        $industries = $this->subject->industries();

        expect($industries)->toBeInstanceOf(Collection::class)
            ->and($industries->isNotEmpty())->toBeTrue()
            ->and($industries->first())->toBeInstanceOf(Industry::class);
    });

    it('fetches_subject_industry_data_for_a_given_industry', function () {
        $slug = 'accounting';

        $subjects = $this->subject->byIndustry($slug);

        expect($subjects)->toBeInstanceOf(Collection::class)
            ->and($subjects->count())->toBeNumeric()->toBeGreaterThan(0)
            ->and($subjects->first()->slug)->toBeString();
    });
});
