<?php

use function lib\common\getScanDir;

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

    $fileList[] = [
        'file' => $filepath,
    ];
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