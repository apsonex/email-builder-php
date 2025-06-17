<?php
namespace Apsonex\EmailBuilderPhp\Contracts;

interface EmailTemplateContract
{
    public function get(string $industry, string $category, string $subject): ?array;
}
