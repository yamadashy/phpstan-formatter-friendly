<?php

declare(strict_types = 1);

namespace Tests\Traits;

/**
 * Support Highlighter and ConsoleColor < 1.0.
 */
trait OldHighlighterSupport
{

    /**
     * @return string
     */
    private function getLineBrakeOrEmptyStringForHighlighter(): string
    {
        $lineBrakeOrEmptyString = "";

        if (class_exists('\JakubOnderka\PhpConsoleHighlighter\Highlighter')) {
            $lineBrakeOrEmptyString = "\n";
        }

        return $lineBrakeOrEmptyString;
    }

}
