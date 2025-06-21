<?php

use Apsonex\EmailBuilderPhp\Support\Blocks\AiBlockConfig;

describe('block_config_test', function () {

    it('block_config_download_fake_response_from_email_builder_dev', function () {
        $response = AiBlockConfig::make()
            ->driver()
            ->endpoint('http://localhost:3099')
            ->fake()
            ->token(env('EMAIL_BUILDER_AUTH_TOKEN'))
            ->withClientOptions([
                'headers' => [
                    'X-Fake-Speed' => '1',
                ]
            ])
            ->query(
                payload: sampleAiBlockConfigPayload()
            );

        expect($response)->toBeInstanceOf(\Apsonex\EmailBuilderPhp\Contracts\StreamedResponse::class);
        expect($response->status)->toBe(200);
        expect($response->headers)->toHaveKey('Content-Type');

        $content = '';

        while (!$response->stream->eof()) {
            $chunk = $response->stream->read(1024);
            $content .= $chunk;
            // dump($chunk);
        }

        // Optionally try to decode JSON chunks
        expect(str($content)->squish()->toString())->toBeString();
    });
});
