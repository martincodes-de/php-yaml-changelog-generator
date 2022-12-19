<?php

include __DIR__ . "/YamlChangelogCreator.php";

$changelogDirectoryPath = __DIR__."/../test-changelog";
$changelogDirectory = scandir($changelogDirectoryPath);
$excludedFiles = [".", "..", "template.yaml", "releaseinfo.yaml"];

$cc = new YamlChangelogCreator($changelogDirectoryPath, $excludedFiles);

var_dump($cc->getChangelog());