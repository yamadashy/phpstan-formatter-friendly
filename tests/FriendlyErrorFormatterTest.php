<?php declare(strict_types=1);

namespace Tests;

use PHPStan\File\FuzzyRelativePathHelper;
use PHPStan\File\NullRelativePathHelper;
use PHPStan\Testing\ErrorFormatterTestCase;
use Tests\TestUtils\StringUtil;
use Yamadashy\PhpStanFormatterFriendly\FriendlyErrorFormatter;

/**
 * @internal
 *
 * @coversDefaultClass \Yamadashy\PhpStanFormatterFriendly\FriendlyErrorFormatter
 */
final class FriendlyErrorFormatterTest extends ErrorFormatterTestCase
{
    public function dataFormatterResultProvider(): iterable
    {
        $currentDir = __DIR__;

        // Error
        yield 'No errors' => [
            0, 0, 0, 0,
            '
 [OK] No errors

',
        ];

        yield 'One file error' => [
            1, 1, 0, 0,
            "  ✘ Foo
  at {$currentDir}/data/AnalysisTargetFoo.php:13
    10|      */
    11|     public function targetFoo()
    12|     {
  > 13|         return 1;
    14|     }
    15|
    16| }


 [ERROR] Found 1 error

",
        ];

        yield 'Two file error' => [
            1, 2, 0, 0,
            "  ✘ Bar
  at {$currentDir}/data/AnalysisTargetBar.php:9
     6| {
     7|
     8|     /**
  >  9|      * @return string
    10|      */
    11|     public function targetBar()
    12|     {

  ✘ Foo
  at {$currentDir}/data/AnalysisTargetFoo.php:13
    10|      */
    11|     public function targetFoo()
    12|     {
  > 13|         return 1;
    14|     }
    15|
    16| }


 [ERROR] Found 2 errors

",
        ];

        // Warning
        yield 'One warning' => [
            1, 0, 0, 1,
            '  ⚠ first warning


 [WARNING] Found 0 errors and 1 warning

',
        ];

        yield 'Two warning' => [
            1, 0, 0, 2,
            '  ⚠ first warning

  ⚠ second warning


 [WARNING] Found 0 errors and 2 warnings

',
        ];

        // Error and warning
        yield 'One Error and one warning' => [
            1, 1, 0, 1,
            "  ✘ Foo
  at {$currentDir}/data/AnalysisTargetFoo.php:13
    10|      */
    11|     public function targetFoo()
    12|     {
  > 13|         return 1;
    14|     }
    15|
    16| }

  ⚠ first warning


 [ERROR] Found 1 error and 1 warning

",
        ];

        // Generic error
        yield 'One generic error' => [
            1, 0, 1, 0,
            '  ✘ first generic error


 [ERROR] Found 1 error

',
        ];

        yield 'Multiple generic errors' => [
            1, 0, 2, 0,
            '  ✘ first generic error

  ✘ second generic error


 [ERROR] Found 2 errors

',
        ];

        yield 'Multiple errors, warnings and generic errors' => [
            1, 2, 2, 2,
            "  ✘ Bar
  at {$currentDir}/data/AnalysisTargetBar.php:9
     6| {
     7|
     8|     /**
  >  9|      * @return string
    10|      */
    11|     public function targetBar()
    12|     {

  ✘ Foo
  at {$currentDir}/data/AnalysisTargetFoo.php:13
    10|      */
    11|     public function targetFoo()
    12|     {
  > 13|         return 1;
    14|     }
    15|
    16| }

  ✘ first generic error

  ✘ second generic error

  ⚠ first warning

  ⚠ second warning


 [ERROR] Found 4 errors and 2 warnings

",
        ];
    }

    /**
     * @dataProvider dataFormatterResultProvider
     * @covers ::formatErrors
     */
    public function testFormatErrors(
        int $expectedExitCode,
        int $numFileErrors,
        int $numGenericErrors,
        int $numWarnings,
        string $expectedOutput
    ): void {
        $relativePathHelper = new FuzzyRelativePathHelper(new NullRelativePathHelper(), '', [], '/');
        $formatter = new FriendlyErrorFormatter($relativePathHelper, 3, 3, null);
        $dummyAnalysisResult = $this->getDummyAnalysisResult($numFileErrors, $numGenericErrors, $numWarnings);

        $exitCode = $formatter->formatErrors($dummyAnalysisResult, $this->getOutput());
        $outputContent = StringUtil::escapeTextColors($this->getOutputContent());
        $outputContent = StringUtil::rtrimByLines($outputContent);

        static::assertSame($expectedExitCode, $exitCode);
        static::assertSame($expectedOutput, $outputContent);
    }

    /**
     * @throws \PHPStan\ShouldNotHappenException
     */
    private function getDummyAnalysisResult(int $numFileErrors, int $numGenericErrors, int $numWarnings): \PHPStan\Command\AnalysisResult
    {
        if ($numFileErrors > 5 || $numFileErrors < 0
            || $numGenericErrors > 2 || $numGenericErrors < 0
            || $numWarnings > 2 || $numWarnings < 0) {
            throw new \PHPStan\ShouldNotHappenException();
        }

        $fileErrors = \array_slice([
            new \PHPStan\Analyser\Error('Foo', __DIR__.'/data/AnalysisTargetFoo.php', 13),
            new \PHPStan\Analyser\Error('Bar', __DIR__.'/data/AnalysisTargetBar.php', 9),
        ], 0, $numFileErrors);
        $genericErrors = \array_slice([
            'first generic error', 'second generic error',
        ], 0, $numGenericErrors);
        $warnings = \array_slice([
            'first warning', 'second warning',
        ], 0, $numWarnings);

        return new \PHPStan\Command\AnalysisResult($fileErrors, $genericErrors, [], $warnings, false, null, true);
    }
}
