<?php

namespace Apsonex\EmailBuilderPhp\Support\Blocks\AiBlockDrivers;

use Apsonex\EmailBuilderPhp\Concerns\Fakeable;
use Apsonex\EmailBuilderPhp\Concerns\HasToken;
use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Concerns\HasEndpoint;
use Apsonex\EmailBuilderPhp\Contracts\HttpQueryDriverContract;

abstract class BaseDriver
{
    use Makebale, Fakeable, HasToken, HasEndpoint;

    protected static string $defaultEndpoint = 'https://ai.emailbuilder.dev';

    protected bool $valid = false;

    protected ?array $response = null;

    protected array $clientOptions = [];

    public function withClientOptions(array $options): static
    {
        $this->clientOptions = $options;
        return $this;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function response(): ?array
    {
        return $this->response;
    }
}
