<?php

namespace Apsonex\EmailBuilderPhp\Support\Prebuilt;

use Apsonex\EmailBuilderPhp\Support\Objects\Blocks\Block;
use Apsonex\EmailBuilderPhp\Support\Objects\Blocks\BlockCategory;
use RuntimeException;
use Illuminate\Support\Collection;
use Apsonex\EmailBuilderPhp\Support\Objects\Category;

class PrebuiltBlock extends BasePrebuilt
{
    /**
     * Get all block categories.
     *
     * @return Collection|Category[]
     * @throws RuntimeException If API request fails or returns invalid response.
     */
    public function categories(): Collection
    {
        $response = $this->query(
            method: 'GET',
            payload: [
                'url' => $this->endpoint . '/prebuilt/blocks/categories',
            ]
        );

        if (($response['status'] ?? null) !== 'success') {
            throw new RuntimeException('Failed to fetch block categories');
        }

        return collect(array_map(
            fn(array $category) => BlockCategory::fromArray($category),
            $response['categories'] ?? []
        ));
    }

    /**
     * Get blocks by category.
     *
     * Queries the endpoint `/prebuilt/blocks/categories/{category}` and returns a collection of BlockDto.
     *
     * @param string $category Category slug to fetch blocks for.
     * @return Collection<Block> Collection of BlockDto instances.
     * @throws \RuntimeException if the API response status is not "success".
     */
    public function category(string $category): Collection
    {
        $url = $this->endpoint . "/prebuilt/blocks/categories/{$category}";

        $response = $this->query(
            method: 'GET',
            payload: ['url' => $url]
        );

        if (($response['status'] ?? null) !== 'success') {
            throw new \RuntimeException("Failed to fetch blocks for category: {$category}");
        }

        $blocks = $response['blocks'] ?? [];

        return collect(array_map(
            fn(array $blockData) => Block::fromArray($blockData),
            $blocks
        ));
    }
}
