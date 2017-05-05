<?php declare(strict_types=1);

namespace Symplify\EasyCodingStandard;

use Nette\Utils\Strings;
use PHP_CodeSniffer\Sniffs\Sniff;
use PhpCsFixer\Fixer\FixerInterface;
use Symfony\Component\Finder\Glob;
use Symplify\EasyCodingStandard\Configuration\Contract\Parameter\ParameterProviderInterface;
use Symplify\EasyCodingStandard\Contract\SkipperInterface;
use Symplify\EasyCodingStandard\Validator\CheckerTypeValidator;

final class Skipper implements SkipperInterface
{
    /**
     * @var string[][]
     */
    private $skipped = [];

    public function __construct(
        ParameterProviderInterface $parameterProvider,
        CheckerTypeValidator $checkerTypeValidator
    ) {
        $skipped = $parameterProvider->provide()['skip'] ?? [];
        $checkerTypeValidator->validate(array_keys($skipped));
        $this->skipped = $skipped;
    }

    /**
     * @param Sniff|FixerInterface|string $checker
     */
    public function shouldSkipCheckerAndFile($checker, string $relativeFilePath): bool
    {
        foreach ($this->skipped as $skippedClass => $skippedFiles) {
            if (! is_a($checker, $skippedClass, true)) {
                continue;
            }

            if ($this->doesFileMatchSkippedFiles($relativeFilePath, $skippedFiles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $skippedFiles
     */
    private function doesFileMatchSkippedFiles(string $relativeFilePath, array $skippedFiles): bool
    {
        foreach ($skippedFiles as $skippedFile) {
            if ($this->fileMatchesPattern($relativeFilePath, $skippedFile)) {
                return true;
            }
        }

        return false;
    }

    private function fileMatchesPattern(string $file, string $ignoredPath): bool
    {
        if ((bool) Strings::match($file, Glob::toRegex($ignoredPath))) {
            return true;
        }

        return Strings::endsWith($file, $ignoredPath);
    }
}