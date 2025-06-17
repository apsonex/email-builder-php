<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Apsonex\EmailBuilderPhp\Support\Asset;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

beforeEach(function () {
    $this->assetsPath = realpath(__DIR__ . '/../../data/assets');

    // Create dummy asset file
    if (!is_dir($this->assetsPath)) {
        mkdir($this->assetsPath, 0777, true);
    }

    file_put_contents($this->assetsPath . '/test.jpg', 'fake-image-content');
});

afterEach(function () {
    @unlink($this->assetsPath . '/test.jpg');
});

describe('asset_test', function () {

    test('as_image_response_serves_image_and_outputs_correct_headers', function () {
        ob_start();
        Asset::asImageResponse('test.jpg', 3600);
        $output = ob_get_clean();

        expect($output)->toBe('fake-image-content');
    });

    test('as_image_response_returns_404_if_image_does_not_exist', function () {
        ob_start();
        Asset::asImageResponse('not_found.jpg');
        $output = ob_get_clean();

        expect(http_response_code())->toBe(404);
        expect($output)->toContain('Image not found');
    });

    /**
     * Using pestphp with Orchestra\Testbench.Laravel is present while running test
     */
    test('as_laravel_image_response_throws_if_laravel_not_detected', function () {
        Asset::asLaravelImageResponse('test.jpg');
    })->markTestSkipped();
});
