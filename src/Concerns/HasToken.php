<?php

namespace Apsonex\EmailBuilderPhp\Concerns;

trait HasToken
{
    protected ?string $token = null;

    public function token(string $token): static
    {
        $this->token = $token;
        return $this;
    }
}
