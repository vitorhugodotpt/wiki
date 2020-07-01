<?php

namespace Pdmfc\Wiki\Traits;

use ParsedownExtra;

trait HasMarkdownParser
{
    /**
     * @param $text
     * @return null|string|string[]
     * @throws \Exception
     */
    public function parse($text)
    {
        return (new ParsedownExtra)->text($text);
    }
}
