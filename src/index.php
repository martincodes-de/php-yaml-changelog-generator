<?php

include __DIR__."/ChangelogCreator.php";

$changelogDirectoryPath = __DIR__."/../changelog";
$changelogDirectory = scandir($changelogDirectoryPath);
$excludedFiles = [".", "..", "template.yaml", "releaseinfo.yaml"];

$files = array_filter($changelogDirectory, fn ($file) => !in_array($file, $excludedFiles));

$changelog = [];

foreach ($files as $fileName) {
    $filePath = $changelogDirectoryPath."/{$fileName}";
    $isDir = is_dir($filePath);

    if (!$isDir) {
        $entry = yaml_parse_file($filePath);
        $addedAtDateTime = date(DATE_ATOM, filemtime($filePath));
        $entry["added_at"] = $addedDateTime;
        $changelog[] = $entry;
    }
}

$cc = new ChangelogCreator($changelogDirectoryPath, $excludedFiles);

var_dump($cc);