<?php declare(strict_types=1);

namespace Symplify\ChangelogLinker\Worker;

use Nette\Utils\Strings;
use Symplify\ChangelogLinker\Contract\Worker\WorkerInterface;
use Symplify\ChangelogLinker\Regex\RegexPattern;

final class DiffLinksToVersionsWorker implements WorkerInterface
{
    /**
     * @var string[]
     */
    private $linkedVersions = [];

    /**
     * @var string[]
     */
    private $versions = [];

    public function processContent(string $content, string $repositoryLink): string
    {
        $this->collectLinkedVersions($content);
        $this->collectVersions($content);

        $linksToAppend = [];
        foreach ($this->versions as $index => $version) {
            if ($this->shouldSkip($version, $index)) {
                continue;
            }

            $linksToAppend[] = sprintf(
                '[%s]: %s/compare/%s...%s',
                $version,
                $repositoryLink,
                $this->versions[$index + 1],
                $version
            );
        }

        if (! count($linksToAppend)) {
            return $content;
        }

        rsort($linksToAppend);

        // append new links to the file
        return $content . PHP_EOL . implode(PHP_EOL, $linksToAppend);
    }

    private function collectLinkedVersions(string $content): void
    {
        $matches = Strings::matchAll($content, '#\[' . RegexPattern::VERSION . '\]: #');
        foreach ($matches as $match) {
            $this->linkedVersions[] = $match['version'];
        }
    }

    private function collectVersions(string $content): void
    {
        $matches = Strings::matchAll($content, '#\#\# \[' . RegexPattern::VERSION . '\]#');
        foreach ($matches as $match) {
            $this->versions[] = $match['version'];
        }
    }

    private function shouldSkip(string $version, int $index): bool
    {
        if (in_array($version, $this->linkedVersions, true)) {
            return true;
        }

        // last version, no previous one
        if (! isset($this->versions[$index + 1])) {
            return true;
        }

        return false;
    }
}
