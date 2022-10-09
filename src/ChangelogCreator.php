<?php

declare(strict_types=1);

class ChangelogCreator
{
    private array $files;
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
        $this->files = $filesWithoutExcludedFiles;
    }

    private function removeExcludedFiles(array $files, array $excludedFiles): array
    {
        return array_filter($files, fn ($file) => !in_array($file, $excludedFiles));
    }
}