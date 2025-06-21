<?php

namespace Apsonex\EmailBuilderPhp\Support\Prebuilt;

use RuntimeException;
use Illuminate\Support\Collection;
use Apsonex\EmailBuilderPhp\Support\Objects\Industry;
use Apsonex\EmailBuilderPhp\Support\Objects\EmailType;
use Apsonex\EmailBuilderPhp\Support\Objects\EmailConfig\EmailConfig;

class PrebuiltEmail extends BasePrebuilt
{

    /**
     * Fetches the list of available email industries from the prebuilt API.
     *
     * @return \Illuminate\Support\Collection<int, \Apsonex\EmailBuilderPhp\Support\Objects\Industry>
     *
     * @throws \RuntimeException If the API response is invalid or the request fails.
     */
    public function industries(): Collection
    {
        $response = $this->query(
            method: 'GET',
            payload: [
                'url' => $this->endpoint . '/prebuilt/emails/industries',
            ]
        );

        if (($response['status'] ?? null) !== 'success') {
            throw new RuntimeException('Failed to fetch industries');
        }

        return collect(array_map(
            fn($industry) => Industry::fromArray($industry),
            $response['industries'] ?? []
        ));
    }

    /**
     * Fetch email types for a given industry. e.g. Marketing, Newsletter etc.
     *
     * Queries the endpoint `/prebuilt/emails/industries/{industry}/types`
     * and returns a collection of EmailType DTOs.
     *
     * @param string $industry The industry slug to fetch types for.
     *
     * @return \Illuminate\Support\Collection<\Apsonex\EmailBuilderPhp\Support\Objects\EmailType>
     *
     * @throws \RuntimeException if the API response status is not "success".
     */
    public function typesByIndustry(string $industry): Collection
    {
        $response = $this->query(
            method: 'GET',
            payload: [
                'url' => $this->endpoint . "/prebuilt/emails/industries/{$industry}/types",
            ]
        );

        if (($response['status'] ?? null) !== 'success') {
            throw new RuntimeException("Failed to fetch email types for industry: {$industry}");
        }

        return collect(array_map(
            fn(array $type) => EmailType::fromArray($type),
            $response['types'] ?? []
        ));
    }

    /**
     * Fetch email config blocks for given industry and type.
     *
     * @param string $industry Industry slug, e.g. 'accounting'
     * @param string $type Type slug, e.g. 'marketing'
     * @return Collection<EmailConfig> Collection of EmailConfig DTOs
     *
     * @throws RuntimeException if response status is not success
     */
    public function config(string $industry, string $type): \Illuminate\Support\Collection
    {
        $response = $this->query(
            method: 'GET',
            payload: [
                'url' => "{$this->endpoint}/prebuilt/emails/industries/{$industry}/types/{$type}",
            ]
        );

        if (($response['status'] ?? null) !== 'success') {
            throw new \RuntimeException('Failed to fetch email config blocks');
        }

        $blocks = $response['blocks'] ?? [];

        return collect(array_map(
            fn(array $block) => EmailConfig::fromArray($block),
            $blocks
        ));
    }
}
