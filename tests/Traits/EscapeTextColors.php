<?php declare(strict_types=1);

namespace Tests\Traits;

trait EscapeTextColors
{
    private function escapeTextColors(string $text): string
    {
        return (string) preg_replace('/\e\[\d+m/', '', $text);
    }
}
