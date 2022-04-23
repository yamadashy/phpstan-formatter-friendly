<?php declare(strict_types=1);

namespace Yamadashy\PhpStanFormatterFriendly;

use PHPStan\Analyser\Error;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\Command\Output;
use PHPStan\File\RelativePathHelper;

class FriendlyErrorFormatter implements ErrorFormatter
{
    /** @var RelativePathHelper */
    private $relativePathHelper;

    /** @var int */
    private $lineBefore;

    /** @var int */
    private $lineAfter;

    public function __construct(RelativePathHelper $relativePathHelper, int $lineBefore, int $lineAfter)
    {
        $this->relativePathHelper = $relativePathHelper;
        $this->lineBefore = $lineBefore;
        $this->lineAfter = $lineAfter;
    }

    /**
     * @return int error code
     */
    public function formatErrors(AnalysisResult $analysisResult, Output $output): int
    {
        if (!$analysisResult->hasErrors() && !$analysisResult->hasWarnings()) {
            $output->getStyle()->success('No errors');

            return 0;
        }

        $this->writeFileSpecificErrors($analysisResult, $output);
        $this->writeNotFileSpecificErrors($analysisResult, $output);
        $this->writeWarnings($analysisResult, $output);
        $this->writeFinalMessage($analysisResult, $output);

        return 1;
    }

    private function writeFileSpecificErrors(AnalysisResult $analysisResult, Output $output): void
    {
        $codeHighlighter = new CodeHighlighter();

        foreach ($analysisResult->getFileSpecificErrors() as $error) {
            $message = $error->getMessage();
            $tip = $this->getFormattedTip($error);
            $filePath = $error->getTraitFilePath() ?? $error->getFilePath();
            $relativeFilePath = $this->relativePathHelper->getRelativePath($filePath);
            $line = $error->getLine();
            $fileContent = null;

            if (file_exists($filePath)) {
                $fileContent = (string) file_get_contents($filePath);
            }

            if (null === $fileContent) {
                $codeSnippet = '  <fg=#888><no such file></>';
            } elseif (null === $line) {
                $codeSnippet = '  <fg=#888><unknown file line></>';
            } else {
                $codeSnippet = $codeHighlighter->highlight($fileContent, $line, $this->lineBefore, $this->lineAfter);
            }

            $output->writeLineFormatted("  <fg=red>✘</> <fg=default;options=bold>{$message}</>");
            if (null !== $tip) {
                $output->writeLineFormatted("  <fg=default>Tip. {$tip}</>");
            }
            $output->writeLineFormatted("  at <fg=cyan>{$relativeFilePath}</>:<fg=cyan>{$line}</>");
            $output->writeLineFormatted($codeSnippet);
            $output->writeLineFormatted('');
        }
    }

    private function writeNotFileSpecificErrors(AnalysisResult $analysisResult, Output $output): void
    {
        foreach ($analysisResult->getNotFileSpecificErrors() as $notFileSpecificError) {
            $output->writeLineFormatted("  <fg=red>✘</> <fg=default;options=bold>{$notFileSpecificError}</>");
            $output->writeLineFormatted('');
        }
    }

    private function writeWarnings(AnalysisResult $analysisResult, Output $output): void
    {
        foreach ($analysisResult->getWarnings() as $warning) {
            $output->writeLineFormatted("  <fg=yellow>⚠</> <fg=default;options=bold>{$warning}</>");
            $output->writeLineFormatted('');
        }
    }

    private function writeFinalMessage(AnalysisResult $analysisResult, Output $output): void
    {
        $warningsCount = \count($analysisResult->getWarnings());
        $finalMessage = sprintf(1 === $analysisResult->getTotalErrorsCount() ? 'Found %d error' : 'Found %d errors', $analysisResult->getTotalErrorsCount());

        if ($warningsCount > 0) {
            $finalMessage .= sprintf(1 === $warningsCount ? ' and %d warning' : ' and %d warnings', $warningsCount);
        }

        if ($analysisResult->getTotalErrorsCount() > 0) {
            $output->getStyle()->error($finalMessage);
        } else {
            $output->getStyle()->warning($finalMessage);
        }
    }

    /**
     * @return null|string
     */
    private function getFormattedTip(Error $error)
    {
        $tip = $error->getTip();

        if (null === $tip) {
            return null;
        }

        return implode("\n    ", explode("\n", $tip));
    }
}
