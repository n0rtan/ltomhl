<?php

namespace progress;

use function lib\arguments\isResetRequested;

$progressLastHashedFile = null;
$progressFileName = '.hashing_progress';

function progressGetLastHashedFile(): ?string
{
    global $progressLastHashedFile;

    return $progressLastHashedFile;
}

function progressAdd($filePath): void
{
    global $progressFileName;

    $currentDir = getcwd();
    $hashingLogFilePath = $currentDir . DIRECTORY_SEPARATOR . $progressFileName;

    $hasgingLogFile = fopen($hashingLogFilePath, 'a+');

    fwrite($hasgingLogFile, $filePath . PHP_EOL);

    fclose($hasgingLogFile);
}

function progressInit(): void
{
    global $progressLastHashedFile, $progressFileName;

    $currentDir = getcwd();
    $hashingLogFilePath = $currentDir . DIRECTORY_SEPARATOR . $progressFileName;

    if (isResetRequested()) {
        $hFile = fopen($hashingLogFilePath, 'w');
        fclose($hFile);
        return;
    }

    if (!file_exists($hashingLogFilePath)) {
        $hFile = fopen($hashingLogFilePath, 'w+');
        fclose($hFile);
    }
   
    $data = file($hashingLogFilePath);
    
    if (empty($data)) {
        return;
    }

    $line = $data[count($data)-1];

    $progressLastHashedFile = trim($line);
}