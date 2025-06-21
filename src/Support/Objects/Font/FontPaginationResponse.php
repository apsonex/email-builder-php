<?php

namespace Apsonex\EmailBuilderPhp\Support\Objects\Font;

class FontPaginationResponse
{
    /**
     * @param FontDTO[] $fonts
     */
    public function __construct(
        public int $page,
        public int $limit,
        public int $total,
        public ?string $showing,
        public ?string $nextPageUrl,
        public ?string $prevPageUrl,
        public ?string $lastPageUrl,
        public array $fonts
    ) {}

    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'limit' => $this->limit,
            'total' => $this->total,
            'showing' => $this->showing,
            'next_page' => $this->nextPageUrl,
            'prev_page' => $this->prevPageUrl,
            'last_page' => $this->lastPageUrl,
            'fonts' => array_map(fn($font) => $font->toArray(), $this->fonts),
        ];
    }
}
