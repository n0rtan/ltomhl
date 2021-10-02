<?php

namespace progress;

use function lib\arguments\isResetRequested;
use function lib\mhl\getNewFileNamePrefix;

$progressLastHashedFile = null;
$progressFileBaseName = 'hashing_progress';

function progressGetLastHashedFile(): ?string
{
    global $progressLastHashedFile;

    return $progressLastHashedFile;
}

function getProgressFileName()
{
    global $progressFileBaseName;

    return '.' . getNewFileNamePrefix() . '_' . $progressFileBaseName;
}

function progressAdd($filePath): void
{
    $progressFileName = getProgressFileName();
    $progressFilePath = getcwd() . DIRECTORY_SEPARATOR . $progressFileName;

    $hasgingLogFile = fopen($progressFilePath, 'a+');

    fwrite($hasgingLogFile, $filePath . PHP_EOL);

    fclose($hasgingLogFile);
}

function progressInit(): void
{
    global $progressLastHashedFile;

    $progressFileName = getProgressFileName();
    $progressFilePath = getcwd() . DIRECTORY_SEPARATOR . $progressFileName;

    if (isResetRequested()) {
        $hFile = fopen($progressFilePath, 'w');
        fclose($hFile);
        return;
    }

    if (!file_exists($progressFilePath)) {
        $hFile = fopen($progressFilePath, 'w+');
        fclose($hFile);
    }
   
    $data = file($progressFilePath);
    
    if (empty($data)) {
        return;
    }

    $line = $data[count($data)-1];

    $progressLastHashedFile = trim($line);
}