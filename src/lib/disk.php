<?php

namespace lib\disk;

use function lib\arguments\getScanDir;
use function lib\log\logMessage;
use function lib\mhl\normalizePath;

$fileList = [];
$filesCount = 0;

function loadFileList(): void
{
    global $fileList, $filesCount;

    read_dir(
        getScanDir()
    );

    $filesCount = count($fileList);
}

function getFileList(): array
{
    global $fileList;

    return $fileList;
}

function collectFiles($dir, $file): void
{
    global $fileList;

    $index = normalizePath($dir) . DIRECTORY_SEPARATOR . $file;

    $fileList[$index] = [];
}

function read_dir($dir): void
{
    $handle = opendir($dir);

    while (($file = readdir($handle)) !== false) {

        if ($file == '.' || $file == '..') {
            continue;
        }

        $filepath = $dir . DIRECTORY_SEPARATOR . $file;

        if (is_link($filepath)) {
            logMessage("Symbolic link detected ({$filepath}). Skipped.");
            continue;
        }

        if (is_file($filepath)) {
            collectFiles($dir, $file);
        } else if (is_dir($filepath)) {
            read_dir($filepath);
        }
    }

    closedir($handle);
}