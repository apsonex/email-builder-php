<?php

namespace Apsonex\EmailBuilderPhp\Support\Prebuilt;

use RuntimeException;
use Illuminate\Support\Collection;
use Apsonex\EmailBuilderPhp\Support\Objects\Industry;
use Apsonex\EmailBuilderPhp\Support\Objects\Subject\SubjectCategory;
use Apsonex\EmailBuilderPhp\Support\Objects\Subject\SubjectIndustry;

class PrebuiltSubject extends BasePrebuilt
{
    /**
     * Fetch industries for subjects.
     *
     * @return \Illuminate\Support\Collection<int, \Apsonex\EmailBuilderPhp\Support\Objects\Industry>
     *
     * @throws RuntimeException if API response status is not success
     */
    public function industries(): Collection
    {
        $response = $this->query(
            method: 'GET',
            payload: [
                'url' => $this->endpoint . '/prebuilt/subjects/industries',
            ]
        );

        if (($response['status'] ?? null) !== 'success') {
            throw new RuntimeException('Failed to fetch industries');
        }

        $industriesData = $response['industries'] ?? [];
        // industries is an associative array with keys as slug, values as industry objects
        // Convert to array of Industry DTOs
        $industries = [];
        foreach ($industriesData as $industry) {
            $industries[] = Industry::fromArray($industry);
        }

        return collect($industries);
    }

    /**
     * Fetch subjects by industry slug from the API.
     *
     * @param string $industry Industry slug (e.g. "accounting")
     * @return SubjectIndustry
     * @throws \RuntimeException if API call fails or response invalid
     */
    public function byIndustry(string $industry): Collection
    {
        $response = $this->query(
            method: 'GET',
            payload: [
                'url' => $this->endpoint . "/prebuilt/subjects/industries/{$industry}",
            ]
        );

        if (($response['status'] ?? null) !== 'success') {
            throw new \RuntimeException('Failed to fetch subjects for industry: ' . $industry);
        }

        return collect($response['subjects'])->map(function ($subjectDetail) {
            return SubjectCategory::fromArray($subjectDetail);
        });
    }
}
