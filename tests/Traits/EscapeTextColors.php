<?php

declare(strict_types = 1);

namespace Tests\Traits;

trait EscapeTextColors
{

    /**
     * @param string $text
     * @return string
     */
    private function escapeTextColors(string $text): string
    {
        return (string) preg_replace('/\e\[\d+m/', '', $text);
    }

}
