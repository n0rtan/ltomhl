<?php

namespace progress;

use function lib\arguments\isResetRequested;
use function lib\mhl\getNewFileNamePrefix;

$progressLastHashedFile = null;
$progressFileBaseName = 'progress';

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

function getProgressfilePath()
{
    return getcwd() . DIRECTORY_SEPARATOR . getProgressFileName();
}

function progressAdd($filePath): void
{ 
    $hasgingLogFile = fopen(getProgressfilePath(), 'a+');

    fwrite($hasgingLogFile, $filePath . PHP_EOL);

    fclose($hasgingLogFile);
}

function progressInit(): void
{
    global $progressLastHashedFile;

    $progressFilePath = getProgressfilePath();

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