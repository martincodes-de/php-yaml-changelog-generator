<?php

$changelogDirectoryPath = __DIR__."/../changelog";
$changelogDirectory = scandir($changelogDirectoryPath);
$excludedFiles = [".", "..", "template.yaml"];

$files = array_filter($changelogDirectory, fn ($file) => !in_array($file, $excludedFiles));

foreach ($files as $fileName) {
    $filePath = $changelogDirectoryPath."/{$fileName}";
    $isDir = is_dir($filePath);

    if (!$isDir) {
        $file = yaml_parse_file($filePath);
        var_dump($file);
    }
}