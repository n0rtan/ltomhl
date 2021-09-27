<?php

namespace lib\disk;

use function lib\arguments\getMhlFilePaths;
use function lib\common\getScanDir;
use function lib\log\logMessage;

$fileList = [];

function loadFileList()
{
    $scanDir = getScanDir();

    read_dir($scanDir);
}

function getFileList()
{
    global $fileList;

    return $fileList;
}

function collectFiles($dir, $file, $filepath)
{
    global $fileList;

    $relativeFilepath = $file;

    if (count(getMhlFilePaths()) > 1) {
        $flashDir = substr($dir, strlen(getScanDir()) + 1);
        $relativeFilepath = $flashDir . DIRECTORY_SEPARATOR . $relativeFilepath;
    } else {
        $flashDir = substr($dir, strlen(getScanDir()) + 1);
        if (strlen($flashDir) > 0) {
            $relativeFilepath = $flashDir . DIRECTORY_SEPARATOR . $relativeFilepath;
        }
    }

    $fileList[$relativeFilepath] = [];
}

function read_dir($dir)
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
            collectFiles($dir, $file, $filepath);
        } else if (is_dir($filepath)) {
            read_dir($filepath);
        }
    }

    closedir($handle);
}