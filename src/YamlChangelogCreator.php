<?php

declare(strict_types=1);

namespace Martincodes\YamlChangelogGenerator;

use Exception;
use Symfony\Component\Yaml\Yaml;

require __DIR__."/../vendor/autoload.php";

class YamlChangelogCreator
{
    /**
     * @var String[]
     */
    private array $changelogDirectoryReleaseDirectories;

    /**
     * @param String[] $excludedFiles
     * @throws Exception
     */
    public function __construct(
        private readonly string $directoryPath,
        private readonly array  $excludedFiles,
        private readonly string $releaseInfoFileName = "releaseinfo.yaml",
        private readonly string $releaseDictionaryNeedle = "",
    )
    {
        $directoryFiles = scandir($directoryPath);
        if ($directoryFiles === false) {
            throw new Exception("Path is not readable and/or not a directory.");
        }

        $filesWithoutExcludedFiles = $this->removeExcludedFiles($directoryFiles, $excludedFiles);
        $this->changelogDirectoryReleaseDirectories = $filesWithoutExcludedFiles;
    }

    /**
     * @return array<int, array<String, mixed>>
     */
    public function getChangelog(): array
    {
        $changelog = [];

        foreach ($this->changelogDirectoryReleaseDirectories as $directory) {
            $filePath = $this->directoryPath."/{$directory}";
            if ($this->isPathAReleaseDirectory($filePath)) {
                $releaseChangelog = $this->generateSingleReleaseChangelog($filePath);
                $releaseTimestamp = (int) $releaseChangelog["release"]["released_at_timestamp"];
                $changelog[$releaseTimestamp] = $releaseChangelog;
            }
        }

        krsort($changelog, SORT_NUMERIC);
        return $changelog;
    }

    /**
     * @param string $directoryPath
     * @return array<String, mixed>
     */
    private function generateSingleReleaseChangelog(string $directoryPath): array
    {
        $changelogFiles = scandir($directoryPath) ?: [];
        $changelogFiles = $this->removeExcludedFiles($changelogFiles, $this->excludedFiles);

        $releaseChangelog = [];
        $releaseChangelog["changes"] = [];
        foreach ($changelogFiles as $file) {
            $filePath = $directoryPath."/{$file}";
            $releaseChangelog["changes"][] = $this->generateEntryFromYamlFile($filePath);
        }
        $releaseChangelog["release"] = $this->generateReleaseInformationFromYamlFile($directoryPath."/{$this->releaseInfoFileName}");

        return $releaseChangelog;
    }

    private function isPathAReleaseDirectory(string $directoryPath): bool
    {
        return is_dir($directoryPath) && str_contains(haystack: $directoryPath, needle: $this->releaseDictionaryNeedle);
    }

    /**
     * @param string $filePath
     * @return array<String, mixed>
     */
    private function generateReleaseInformationFromYamlFile(string $filePath): array
    {
        $releaseInformation = Yaml::parseFile($filePath);
        $releaseDate = date(DATE_ATOM, $releaseInformation["released_at"]);
        $releaseDateAsTimestamp = strtotime($releaseDate);
        $releaseInformation["released_at_timestamp"] = $releaseDateAsTimestamp;
        $releaseInformation["released_at"] = $releaseDate;

        return $releaseInformation;
    }

    /**
     * @param string $filePath
     * @return array<String, mixed>
     */
    private function generateEntryFromYamlFile(string $filePath): array
    {
        $entry = Yaml::parseFile($filePath);
        $modificationTimestamp = filemtime($filePath) ?: time();
        $addedAtDateTime = date(DATE_ATOM, $modificationTimestamp);
        $entry["added_at"] = $addedAtDateTime;
        return $entry;
    }

    /**
     * @param String[] $files
     * @param String[] $excludedFiles
     * @return String[]
     */
    private function removeExcludedFiles(array $files, array $excludedFiles): array
    {
        $excludedFiles = [".", "..", ...$excludedFiles];
        return array_filter($files, fn ($file) => !in_array($file, $excludedFiles));
    }
}