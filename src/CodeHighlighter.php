<?php

declare(strict_types=1);

namespace Yamadashy\PhpStanFormatterFriendly;

use PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;
use PHP_Parallel_Lint\PhpConsoleHighlighter\Highlighter;

class CodeHighlighter
{

    /** @var Highlighter */
    private $highlighter;

    public function __construct()
    {
        $colors = new ConsoleColor();

        $this->highlighter = new Highlighter($colors);
    }

    /**
     * @param string $fileContent
     * @param int $lineNumber
     * @param int $lineBefore
     * @param int $lineAfter
     * @return string
     * @throws \PHP_Parallel_Lint\PhpConsoleColor\InvalidStyleException
     */
    public function highlight(string $fileContent, int $lineNumber, int $lineBefore, int $lineAfter): string
    {
        return $this->highlighter->getCodeSnippet(
            $fileContent,
            $lineNumber,
            $lineBefore,
            $lineAfter
        );
    }

}
