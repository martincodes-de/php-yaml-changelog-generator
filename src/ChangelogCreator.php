<?php

declare(strict_types=1);

class ChangelogCreator
{
    private array $changelogDirectoryReleaseDirectories;

    /**
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

    public function getChangelog(): array
    {
        $changelog = [];

        foreach ($this->changelogDirectoryReleaseDirectories as $directory) {
            $filePath = $this->directoryPath."/{$directory}";
            if ($this->isPathAReleaseDirectory($filePath)) {
                $releaseChangelog = $this->generateSingleReleaseChangelog($filePath);
                $releaseTimestamp = $releaseChangelog["info"]["released_at_timestamp"];
                $changelog[$releaseTimestamp] = $releaseChangelog;
            }
        }

        krsort($changelog, SORT_NUMERIC);
        return $changelog;
    }

    private function generateSingleReleaseChangelog(string $directoryPath): array
    {
        $changelogFiles = scandir($directoryPath);
        $changelogFiles = $this->removeExcludedFiles($changelogFiles, $this->excludedFiles);

        $releaseChangelog = [];
        foreach ($changelogFiles as $file) {
            $filePath = $directoryPath."/{$file}";
            $releaseChangelog["changes"][] = $this->generateEntryFromYamlFile($filePath);
        }
        $releaseChangelog["info"] = $this->generateReleaseInformationFromYamlFile($directoryPath."/{$this->releaseInfoFileName}");

        return $releaseChangelog;
    }

    private function isPathAReleaseDirectory(string $directoryPath): bool
    {
        return is_dir($directoryPath) && str_contains(haystack: $directoryPath, needle: $this->releaseDictionaryNeedle);
    }

    private function generateReleaseInformationFromYamlFile(string $filePath): array
    {
        $releaseInformation = yaml_parse_file($filePath);

        $releaseDate = strtotime($releaseInformation["released_at"]);
        $releaseDateAsTimestamp = $releaseDate ? $releaseDate : time();
        $releaseInformation["released_at_timestamp"] = $releaseDateAsTimestamp;

        return $releaseInformation;
    }

    private function generateEntryFromYamlFile(string $filePath): array
    {
        $entry = yaml_parse_file($filePath);
        $addedAtDateTime = date(DATE_ATOM, filemtime($filePath));
        $entry["added_at"] = $addedAtDateTime;
        return $entry;
    }

    private function removeExcludedFiles(array $files, array $excludedFiles): array
    {
        return array_filter($files, fn ($file) => !in_array($file, $excludedFiles));
    }
}