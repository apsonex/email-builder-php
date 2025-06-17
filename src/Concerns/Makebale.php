<?php
namespace Apsonex\EmailBuilderPhp\Concerns;

trait Makebale
{
    public static function make(): static
    {
        return new static();
    }
}
