<?php

namespace Apsonex\EmailBuilderPhp\Support;

use Apsonex\Font\Font as BaseFont;

/**
 * https://github.com/apsonex/font
 * @see \Apsonex\Font\Font
 */
class FontFinder extends BaseFont
{
    public function __construct()
    {
        $this->bunny();
    }
}
