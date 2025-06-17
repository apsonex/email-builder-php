<?php

namespace Apsonex\EmailBuilderPhp\Support;

use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Contracts\EmailTemplateContract;

class EmailTemplate implements EmailTemplateContract
{
    use Makebale;

    /**
     * Usage example:
     * get('accounting', 'marketing', 'exclusive-offer-free-financial-consultation')
     *
     * Returns an associative array with the structure:
     *
     * @return array{
     *   name: string,
     *   type: string,
     *   industry: string,
     *   category: string,
     *   config: array{
     *     head: array{
     *       breakpoint: string,
     *       fonts: array<mixed>
     *     },
     *     body: array{
     *       id: string,
     *       type: string,
     *       config: array<string, mixed>,
     *       content: array<mixed>
     *     }
     *   }
     * }|null
     */
    public function get(string $industry, string $category, string $subject): ?array
    {
        $file = Data::path("email-configs/templates/{$industry}/{$category}/{$subject}.json");

        return is_file($file) ? json_decode(file_get_contents($file), true) : null;
    }
}
