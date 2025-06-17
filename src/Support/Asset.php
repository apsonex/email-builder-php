<?php

namespace Apsonex\EmailBuilderPhp\Support;

class Asset
{

    public static function path($path = ''): string
    {
        $path = $path ? trim($path, '/') : null;

        return Data::path('assets' . ($path ? '/' . $path : ''));
    }

    /**
     * Serve an image response using vanilla PHP, with browser caching.
     *
     * @param string|null $imgLoc        Relative path to the image.
     * @param int         $cacheSeconds  Cache time in seconds (default 1 year).
     *
     * @return void
     */
    public static function asImageResponse($imgLoc = null, int $cacheSeconds = 31536000): void
    {
        $imgPath = static::path(trim($imgLoc, '/'));

        if (!file_exists($imgPath)) {
            http_response_code(404);
            echo 'Image not found';
            return;
        }

        $mimeType = mime_content_type($imgPath);
        $lastModified = gmdate('D, d M Y H:i:s', filemtime($imgPath)) . ' GMT';
        $etag = md5_file($imgPath);

        // Headers
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($imgPath));
        header('Content-Disposition: inline; filename="' . basename($imgPath) . '"');
        header('Cache-Control: public, max-age=' . $cacheSeconds);
        header('Last-Modified: ' . $lastModified);
        header('ETag: ' . $etag);

        // 304 Not Modified check
        if (
            isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
            $_SERVER['HTTP_IF_MODIFIED_SINCE'] === $lastModified &&
            isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
            $_SERVER['HTTP_IF_NONE_MATCH'] === $etag
        ) {
            http_response_code(304);
            return;
        }

        readfile($imgPath);
    }

    /**
     * Serve an image response using Laravel's Response facade, with caching.
     *
     * @param string|null $imgLoc        Relative path to the image.
     * @param int         $cacheSeconds  Cache time in seconds (default 1 year).
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     *
     * @throws \Exception If Laravel is not detected.
     */
    public static function asLaravelImageResponse($imgLoc = null, int $cacheSeconds = 31536000)
    {
        if (!class_exists(\Illuminate\Support\Facades\Response::class)) {
            throw new \Exception('Laravel not detected. Use asImageResponse() instead.');
        }

        $imgPath = static::path(trim($imgLoc, '/'));

        if (!file_exists($imgPath)) {
            abort(404, 'Image not found');
        }

        $lastModified = gmdate('D, d M Y H:i:s', filemtime($imgPath)) . ' GMT';
        $etag = md5_file($imgPath);

        $headers = [
            'Content-Type' => mime_content_type($imgPath),
            'Content-Disposition' => 'inline; filename="' . basename($imgPath) . '"',
            'Cache-Control' => 'public, max-age=' . $cacheSeconds,
            'Last-Modified' => $lastModified,
            'ETag' => $etag,
        ];

        // 304 Not Modified
        if (
            request()->header('If-Modified-Since') === $lastModified &&
            request()->header('If-None-Match') === $etag
        ) {
            return response('', 304, $headers);
        }

        return \Illuminate\Support\Facades\Response::file($imgPath, $headers);
    }
}
