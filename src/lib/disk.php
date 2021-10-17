<?php

namespace lib\disk;

use function lib\arguments\getScanDir;
use function lib\arguments\isResetRequested;
use function lib\log\logMessage;
use function lib\mhl\getNewFileNamePrefix;
use function lib\mhl\normalizePath;

$fileList = [];
$filesCount = 0;

$fileListBaseFileName = 'filelist';

function getFileListFilePath()
{
    return getcwd() . DIRECTORY_SEPARATOR . getFileListFileName();
}

function getFileListFileName()
{
    global $fileListBaseFileName;

    return '.' . getNewFileNamePrefix() . '_' . $fileListBaseFileName;
}

function loadFileList(): bool
{
    global $fileList, $filesCount;

    $filePath = getFileListFilePath();

    if (isResetRequested()) {
        $hfile = fopen($filePath, 'w');
        fclose($hfile);
    } else if (is_readable($filePath)) {
        $fileList = json_decode(file_get_contents($filePath), true);
        $filesCount = count($fileList);
    }
    
    return $filesCount > 0;
}

function saveFileList(): void
{
    file_put_contents(
        getFileListFilePath(),
        json_encode(getFileList())
    );
}

function readFileList(): void
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

        if (preg_match('#^\.#', $file)) {
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