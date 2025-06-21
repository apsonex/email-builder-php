<?php

namespace Apsonex\EmailBuilderPhp\Support\AiPayload;

use Apsonex\EmailBuilderPhp\Contracts\ArrayValueContract;

class StockImagesProviderApiKey implements ArrayValueContract
{
    public function __construct(
        protected ?string $pexelsApiKey = null,
        protected ?string $pixabayApiKey = null,
        protected ?string $unsplashApiKey = null,
    ) {
        //
    }

    public static function make(
        ?string $pexelsApiKey = null,
        ?string $pixabayApiKey = null,
        ?string $unsplashApiKey = null,
    ): static {
        return new static(
            pexelsApiKey: $pexelsApiKey,
            pixabayApiKey: $pixabayApiKey,
            unsplashApiKey: $unsplashApiKey,
        );
    }

    public function value(): array
    {
        $values = array_filter([
            'pexels' => $this->pexelsApiKey,
            'pixabay' => $this->pixabayApiKey,
            'unsplash' => $this->unsplashApiKey,
        ]);

        return count($values) > 0 ? $values : [];
    }
}
