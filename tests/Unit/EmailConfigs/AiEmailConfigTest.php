<?php

use Apsonex\EmailBuilderPhp\Support\EmailConfigs\AiEmailConfig;

describe('ai_email_config_test', function () {

    it('it_download_fake_response_from_email_builder_dev', function () {

        $resposne = AiEmailConfig::make()
            ->driver()
            ->fake()
            ->query(
                payload: sampleEmailConfigPayload()
            );

        expect($resposne->isValid())->toBeTrue();
        expect($resposne->response())->toBeArray();
    });
});
