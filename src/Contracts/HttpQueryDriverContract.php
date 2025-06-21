<?php

namespace Apsonex\EmailBuilderPhp\Contracts;

use Illuminate\Contracts\Support\Arrayable;

interface HttpQueryDriverContract
{
    public static function make(): static;

    public function fake(int $fakeType = 200): static;

    public function token(string $token): static;

    public function endpoint(string $endpoint): static;

    public function withClientOptions(array $options): static;

    public function query(array|Arrayable $payload): StreamedResponse;

    public function isValid(): bool;

    public function response(): ?array;
}
