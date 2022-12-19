<?php

include __DIR__."/ChangelogCreator.php";

$changelogDirectoryPath = __DIR__."/../test-changelog";
$changelogDirectory = scandir($changelogDirectoryPath);
$excludedFiles = [".", "..", "template.yaml", "releaseinfo.yaml"];

$cc = new ChangelogCreator($changelogDirectoryPath, $excludedFiles);

var_dump($cc->getChangelog());