<?php declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Error;

use Nette\Utils\Arrays;
use Symplify\EasyCodingStandard\ChangedFilesDetector\ChangedFilesDetector;

final class ErrorAndDiffCollector
{
    /**
     * @var Error[][]
     */
    private $errors = [];

    /**
     * @var ChangedFilesDetector
     */
    private $changedFilesDetector;

    /**
     * @var FileDiff[][]
     */
    private $fileDiffs = [];

    /**
     * @var ErrorSorter
     */
    private $errorSorter;

    public function __construct(ChangedFilesDetector $changedFilesDetector, ErrorSorter $errorSorter)
    {
        $this->changedFilesDetector = $changedFilesDetector;
        $this->errorSorter = $errorSorter;
    }

    public function addErrorMessage(string $filePath, int $line, string $message, string $sourceClass): void
    {
        $this->changedFilesDetector->invalidateFile($filePath);

        $this->errors[$filePath][] = Error::createFromLineMessageSourceClass($line, $message, $sourceClass);
    }

    /**
     * @return Error[][]
     */
    public function getErrors(): array
    {
        return $this->errorSorter->sortByFileAndLine($this->errors);
    }

    public function getErrorCount(): int
    {
        return count(Arrays::flatten($this->errors));
    }

    /**
     * @param string[] $appliedCheckers
     */
    public function addDiffForFile(string $filePath, string $diff, array $appliedCheckers): void
    {
        $this->changedFilesDetector->invalidateFile($filePath);

        $this->fileDiffs[$filePath][] = FileDiff::createFromDiffAndAppliedCheckers($diff, $appliedCheckers);
    }

    public function getFileDiffsCount(): int
    {
        return count(Arrays::flatten($this->getFileDiffs()));
    }

    /**
     * @return FileDiff[][]
     */
    public function getFileDiffs(): array
    {
        return $this->fileDiffs;
    }

    /**
     * Used by external sniff/fixer testing classes
     */
    public function resetCounters(): void
    {
        $this->errors = [];
        $this->fileDiffs = [];
    }
}
