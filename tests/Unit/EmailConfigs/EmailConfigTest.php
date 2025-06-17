<?php

use Apsonex\EmailBuilderPhp\Support\EmailConfigs\EmailConfig;

describe('email_config_test', function () {

    it('it_download_fake_response_from_email_builder_dev', function () {

        $resposne = EmailConfig::make()
            ->driver()
            ->fake()
            ->query(
                payload: sampleEmailConfigPayload()
            );

        expect($resposne->isValid())->toBeTrue();
        expect($resposne->response())->toBeArray();
    });
});
