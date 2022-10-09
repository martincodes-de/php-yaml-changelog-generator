<?php

declare(strict_types=1);

class ChangelogCreator
{
    private array $changelogDirectoryFiles;
    /**
     * @throws Exception
     */
    public function __construct(
        private readonly string $directoryPath,
        private readonly array $excludedFiles,
    )
    {
        $directoryFiles = scandir($directoryPath);
        if ($directoryFiles === false) {
            throw new Exception("Path is not readable and/or not a directory.");
        }

        $filesWithoutExcludedFiles = $this->removeExcludedFiles($directoryFiles, $excludedFiles);
        $this->changelogDirectoryFiles = $filesWithoutExcludedFiles;
    }

    public function getChangelog(): array
    {
        $changelog = [];

        foreach ($this->changelogDirectoryFiles as $file) {
            $filePath = $this->directoryPath."/{$file}";
            if ($this->isPathAReleaseDirectory($filePath)) {
                $releaseChangelog = $this->generateSingleReleaseChangelog($filePath);
                $releaseName = $releaseChangelog["info"]["name"];
                $changelog[$releaseName] = $releaseChangelog;
            }
        }

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
        $releaseChangelog["info"] = $this->generateReleaseInformationFromYamlFile($directoryPath."/releaseinfo.yaml");

        return $releaseChangelog;
    }

    private function isPathAReleaseDirectory(string $directoryPath): bool
    {
        return is_dir($directoryPath) && str_contains(haystack: $directoryPath, needle: "fs-release");
    }

    private function generateReleaseInformationFromYamlFile(string $filePath): array
    {
        return yaml_parse_file($filePath);
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