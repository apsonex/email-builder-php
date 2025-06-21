<?php

namespace Apsonex\EmailBuilderPhp\Contracts;

use Psr\Http\Message\StreamInterface;

class StreamedResponse
{
    public function __construct(
        public StreamInterface $stream,
        public array $headers = [],
        public int $status = 200
    ) {}

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }

        while (!$this->stream->eof()) {
            echo $this->stream->read(1024);
            flush(); // flush to browser
        }
    }
}
