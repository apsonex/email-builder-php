<?php

namespace Apsonex\EmailBuilderPhp\Support;

use RuntimeException;
use Illuminate\Support\Arr;
use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Concerns\HasHttpClient;
use Apsonex\EmailBuilderPhp\Support\Objects\Font\FontObject;
use Apsonex\EmailBuilderPhp\Support\Objects\Font\FontPaginationResponse;

class Font
{
    use Makebale, HasHttpClient;

    /**
     * Fetch a paginated list of fonts.
     *
     * @param int $page
     * @param int $limit
     * @return FontPaginationResponse
     */
    public function index(int $page = 1, int $limit = 20): FontPaginationResponse
    {
        $response = $this->query(
            method: 'GET',
            payload: [
                'url' => '/fonts',
                'data' => [ // sent as JSON body (GET+body is valid in Guzzle but unconventional)
                    'page' => $page,
                    'limit' => $limit,
                ],
            ]
        );

        if (($response['status'] ?? null) !== 'success') {
            throw new RuntimeException('Failed to fetch font list.');
        }

        $fonts = array_map(
            fn(array $font) => FontObject::fromArray($font),
            $response['fonts'] ?? []
        );

        return new FontPaginationResponse(
            page: $response['page'] ?? $page,
            limit: $response['limit'] ?? $limit,
            total: $response['total'] ?? 0,
            showing: $response['showing'] ?? null,
            nextPageUrl: $response['next_page'] ?? null,
            prevPageUrl: $response['prev_page'] ?? null,
            lastPageUrl: $response['last_page'] ?? null,
            fonts: $fonts
        );
    }

    /**
     * Search fonts by keyword.
     *
     * @param string $query
     * @param int $page
     * @param int $limit
     * @return FontPaginationResponse
     *
     * @throws \RuntimeException
     */
    public function search(string $query, int $page = 1, int $limit = 20): FontPaginationResponse
    {
        $response = $this->query(
            method: 'GET',
            payload: [
                'url' => '/fonts/search',
                'data' => [
                    'q' => $query,
                    'page' => $page,
                    'limit' => $limit,
                ],
            ]
        );

        if (($response['status'] ?? null) !== 'success') {
            throw new \RuntimeException('Failed to search fonts.');
        }

        $fonts = array_map(
            fn(array $font) => \Apsonex\EmailBuilderPhp\Support\Objects\Font\FontObject::fromArray($font),
            $response['fonts'] ?? []
        );

        return new \Apsonex\EmailBuilderPhp\Support\Objects\Font\FontPaginationResponse(
            page: $response['page'] ?? $page,
            limit: $response['limit'] ?? $limit,
            total: $response['total'] ?? 0,
            showing: $response['showing'] ?? null,
            nextPageUrl: $response['next_page'] ?? null,
            prevPageUrl: $response['prev_page'] ?? null,
            lastPageUrl: $response['last_page'] ?? null,
            fonts: $fonts
        );
    }

    /**
     * Find fonts by multiple keys.
     *
     * @param string|string[] $keys Font keys (max 100).
     * @param int $limit Maximum number of fonts to return (default 20).
     *
     * @throws \InvalidArgumentException If more than 100 keys are provided.
     * @throws \RuntimeException If the API request fails or returns an error.
     *
     * @return \Illuminate\Support\Collection<int, \Apsonex\EmailBuilderPhp\Support\Objects\Font\FontObject>
     */
    public function byKeys(string|array $keys, int $limit = 20): \Illuminate\Support\Collection
    {
        if (count($keys) > 100) {
            throw new \InvalidArgumentException('Maximum of 100 keys are allowed.');
        }

        $response = $this->query('GET', [
            'url' => '/fonts/keys?keys=' . rawurlencode(implode(',', Arr::wrap($keys))) . '&limit=' . $limit,
        ]);

        if (($response['status'] ?? null) !== 'success') {
            throw new \RuntimeException('Failed to fetch fonts by keys.');
        }

        return collect(array_map(
            fn(array $font) => FontObject::fromArray($font),
            $response['fonts'] ?? []
        ));
    }

    /**
     * Find font by exact family name.
     *
     * @param string $family Font family name (case-insensitive)
     * @return FontObject|null Returns the first matched FontObject or null if none found
     *
     * @throws RuntimeException If the API request fails
     */
    public function byFamily(string $family): ?FontObject
    {
        $encodedFamily = rawurlencode($family);

        $response = $this->query('GET', [
            'url' => "/fonts/family/{$encodedFamily}",
        ]);

        if (($response['status'] ?? null) !== 'success' && !is_array($response['font'] ?? null)) {
            throw new RuntimeException("Failed to fetch fonts for family: {$family}");
        }

        return FontObject::fromArray($response['font']);
    }

    /**
     * Find fonts by multiple families.
     *
     * @param string|array $families Comma-separated string or array of family names.
     * @param int $limit Maximum number of fonts to return (default 20).
     * @return \Illuminate\Support\Collection Collection of FontObject instances.
     *
     * @throws \RuntimeException If the API request fails.
     */
    public function byFamilies(string|array $families, int $limit = 20): \Illuminate\Support\Collection
    {
        // Normalize to array and trim each family name
        $familyList = collect(is_array($families) ? $families : explode(',', $families))
            ->map(fn($f) => trim($f))
            ->filter(fn($f) => $f !== '');

        if ($familyList->isEmpty()) {
            return collect();
        }

        // Lowercase for matching
        $lowerFamilies = $familyList->map(fn($f) => strtolower($f))->all();

        // Query API endpoint with URL encoded list joined by commas
        $encodedFamilies = rawurlencode($familyList->implode(','));

        $response = $this->query('GET', [
            'url' => "/fonts/families/?families={$encodedFamilies}&limit={$limit}",
        ]);

        if (($response['status'] ?? null) !== 'success') {
            throw new \RuntimeException('Failed to fetch fonts for families: ' . $familyList->implode(', '));
        }

        // Map API response fonts to FontObject DTOs
        $fonts = collect($response['fonts'] ?? [])
            ->map(fn(array $font) => FontObject::fromArray($font))
            // Optional: filter in PHP just to be sure (usually API handles)
            ->filter(fn(FontObject $font) => in_array(strtolower($font->family), $lowerFamilies))
            ->take($limit);

        return $fonts->values();
    }


    /**
     * Get fonts by category/type with pagination.
     *
     * @param string $type Font category/type (e.g., 'sans-serif', 'serif').
     * @param int $page Page number (default 1).
     * @param int $limit Number of items per page (default 20).
     * @return FontPaginationResponse
     *
     * @throws RuntimeException
     */
    public function byType(string $type, int $page = 1, int $limit = 20): FontPaginationResponse
    {
        $type = strtolower($type);

        $response = $this->query('GET', [
            'url' => '/fonts/type/' . rawurlencode($type),
            'data' => [
                'page' => $page,
                'limit' => $limit,
            ],
        ]);

        if (($response['status'] ?? null) !== 'success') {
            throw new RuntimeException("Failed to fetch fonts for type: {$type}");
        }

        $fonts = array_map(
            fn(array $font) => FontObject::fromArray($font),
            $response['fonts'] ?? []
        );

        return new FontPaginationResponse(
            page: $response['page'] ?? $page,
            limit: $response['limit'] ?? $limit,
            total: $response['total'] ?? 0,
            showing: $response['showing'] ?? null,
            nextPageUrl: $response['next_page'] ?? null,
            prevPageUrl: $response['prev_page'] ?? null,
            lastPageUrl: $response['last_page'] ?? null,
            fonts: $fonts
        );
    }
}
