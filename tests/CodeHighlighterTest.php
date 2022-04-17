<?php

declare(strict_types = 1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Traits\EscapeTextColors;
use Tests\Traits\OldHighlighterSupport;
use Yamadashy\PhpStanFormatterFriendly\CodeHighlighter;

/**
 * @coversDefaultClass \Yamadashy\PhpStanFormatterFriendly\CodeHighlighter
 */
class CodeHighlighterTest extends TestCase
{

    use EscapeTextColors,
        OldHighlighterSupport;

    public function dataResultProvider(): iterable
    {
        $lineBrakeOrEmptyString = $this->getLineBrakeOrEmptyStringForHighlighter();

        yield 'show 3 lines before and after' => [
            __DIR__ . '/data/AnalysisTargetFoo.php',
            11,
            3,
            3,
            '     8|     /**
     9|      * @return string
    10|      */
  > 11|     public function targetFoo()
    12|     {
    13|         return 1;
    14|     }'.$lineBrakeOrEmptyString,
        ];

        yield 'show 5 lines before' => [
            __DIR__ . '/data/AnalysisTargetFoo.php',
            11,
            5,
            3,
            '     6| {
     7| 
     8|     /**
     9|      * @return string
    10|      */
  > 11|     public function targetFoo()
    12|     {
    13|         return 1;
    14|     }'.$lineBrakeOrEmptyString,
        ];

        yield 'show 6 lines after' => [
            __DIR__ . '/data/AnalysisTargetFoo.php',
            11,
            3,
            6,
            '     8|     /**
     9|      * @return string
    10|      */
  > 11|     public function targetFoo()
    12|     {
    13|         return 1;
    14|     }
    15| 
    16| }
    17| '.$lineBrakeOrEmptyString
        ];

        yield 'show 1 line only' => [
            __DIR__ . '/data/AnalysisTargetFoo.php',
            11,
            0,
            0,
            '  > 11|     public function targetFoo()'.$lineBrakeOrEmptyString
        ];

        yield 'show 1 line of Bar' => [
            __DIR__ . '/data/AnalysisTargetBar.php',
            13,
            0,
            0,
            '  > 13|         return 2;'.$lineBrakeOrEmptyString
        ];
    }

    /**
     * @dataProvider dataResultProvider
     * @covers ::highlight
     */
    public function testHighlight(
        string $filePath,
        int $lineNumber,
        int $lineBefore,
        int $lineAfter,
        string $expectedOutput
    ): void
    {
        $codeHighlighter = new CodeHighlighter();

        $fileContent = (string) file_get_contents($filePath);

        $output = $codeHighlighter->highlight($fileContent, $lineNumber, $lineBefore, $lineAfter);
        $output = $this->escapeTextColors($output);

        $this->assertEquals($expectedOutput, $output);
    }

}
