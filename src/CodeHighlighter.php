<?php

declare(strict_types=1);

namespace Yamadashy\PhpStanFormatterFriendly;

use JakubOnderka\PhpConsoleColor\ConsoleColor as OldConsoleColor;
use JakubOnderka\PhpConsoleHighlighter\Highlighter as OldHighlighter;
use PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;
use PHP_Parallel_Lint\PhpConsoleHighlighter\Highlighter;

class CodeHighlighter
{

    /** @var OldHighlighter|Highlighter  */
    private $highlighter;

    public function __construct()
    {
        if (
            class_exists('\PHP_Parallel_Lint\PhpConsoleHighlighter\Highlighter')
            && class_exists('\PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor')
        ) {
            // Support Highlighter and ConsoleColor 1.0+.
            $colors = new ConsoleColor();
            $this->highlighter = new Highlighter($colors);
        } else if (
            class_exists('\JakubOnderka\PhpConsoleHighlighter\Highlighter')
            && class_exists('\JakubOnderka\PhpConsoleColor\ConsoleColor')
        ) {
            // Support Highlighter and ConsoleColor < 1.0.
            $colors = new OldConsoleColor();
            $this->highlighter = new OldHighlighter($colors);
        }
    }

    /**
     * @param string $fileContent
     * @param int $lineNumber
     * @param int $lineBefore
     * @param int $lineAfter
     * @return string
     */
    public function highlight(string $fileContent, int $lineNumber, int $lineBefore, int $lineAfter): string
    {
        $content = $this->highlighter->getCodeSnippet(
            $fileContent,
            $lineNumber,
            $lineBefore,
            $lineAfter
        );

        return rtrim($content, "\n");
    }

}
