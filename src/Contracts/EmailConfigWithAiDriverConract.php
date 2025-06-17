<?php

namespace Apsonex\EmailBuilderPhp\Contracts;

interface EmailConfigWithAiDriverConract
{
    public static function make(): static;

    public function fake(int $fakeType = 200): static;

    public function query(array $payload, array $headers = []): static;

    public function isValid(): bool;

    public function response(): ?array;
}
