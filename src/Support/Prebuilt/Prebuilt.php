<?php

namespace Apsonex\EmailBuilderPhp\Support\Prebuilt;

use Apsonex\EmailBuilderPhp\Concerns\HasToken;

class Prebuilt
{
    use HasToken;

    protected string $endpoint = 'https://ai.emailbuilder.dev';

    protected ?PrebuiltBlock $block = null;

    protected ?PrebuiltEmail $email = null;

    protected ?PrebuiltSubject $subject = null;

    public function endpoint(string $endpoint): static
    {
        $this->endpoint = trim($endpoint, '/');
        return $this;
    }

    public function block(): PrebuiltBlock
    {
        return $this->block ??= PrebuiltBlock::make()->endpoint($this->endpoint)->token($this->token);
    }

    public function email(): PrebuiltEmail
    {
        return $this->email ??= PrebuiltEmail::make()->endpoint($this->endpoint)->token($this->token);
    }

    public function subject(): PrebuiltSubject
    {
        return $this->subject ??= PrebuiltSubject::make()->endpoint($this->endpoint)->token($this->token);
    }
}
