<?php

namespace Apsonex\EmailBuilderPhp\Support\AiPayload;

use Apsonex\EmailBuilderPhp\Contracts\ArrayValueContract;

class ApiKey implements ArrayValueContract
{
    public function __construct(
        protected string $apiKey,
        protected ?string $orgId = null,
        protected ?string $projectId = null,
    ) {
        //
    }

    public static function make(
        string $apiKey,
        ?string $orgId = null,
        ?string $projectId = null,
    ): static {
        return new static(
            apiKey: $apiKey,
            orgId: $orgId,
            projectId: $projectId,
        );
    }

    public function value(): array
    {
        return array_filter([
            'aiApiKey' => $this->apiKey,
            'orgId' => $this->orgId,
            'projectId' => $this->projectId,
        ]);
    }
}
