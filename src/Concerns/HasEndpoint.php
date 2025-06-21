<?php

namespace Apsonex\EmailBuilderPhp\Concerns;

trait HasEndpoint
{
    protected ?string $endpoint = null;

    public function endpoint(string $endpoint): static
    {
        $this->endpoint = $endpoint;
        return $this;
    }
}
