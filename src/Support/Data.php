<?php

namespace Apsonex\EmailBuilderPhp\Support;

class Data
{
    public static function path($path = ''): string
    {
        return realpath(__DIR__ . '/../../data') . ($path ? '/' . $path : '');
    }
}
