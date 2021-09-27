<?php

use function lib\arguments\isResetRequested;

$lastHashedFile = null;
$hashingProgressFileName = '.hashing_progress';

function getLastHashedFile(): ?string
{
    global $lastHashedFile;

    return $lastHashedFile;
}

function loadOrCreateHashingLog()
{
    global $lastHashedFile, $hashingProgressFileName;

    if (isResetRequested()) {
        $currentDir = getcwd();
        $hashingLogFilePath = $currentDir . DIRECTORY_SEPARATOR . $hashingProgressFileName;
        $hFile = fopen($hashingLogFilePath, 'w');
        fclose($hFile);
        return;
    }

    $currentDir = getcwd();
    $hashingLogFilePath = $currentDir . DIRECTORY_SEPARATOR . $hashingProgressFileName;
    
    $data = file($hashingLogFilePath);
    
    if (empty($data)) {
        return;
    }

    $line = $data[count($data)-1];

    $lastHashedFile = trim($line);
}

function logProgress($filePath)
{
    global $hashingProgressFileName;

    $currentDir = getcwd();
    $hashingLogFilePath = $currentDir . DIRECTORY_SEPARATOR . $hashingProgressFileName;

    $hasgingLogFile = fopen($hashingLogFilePath, 'a+');

    fwrite($hasgingLogFile, $filePath . PHP_EOL);

    fclose($hasgingLogFile);
}

function logMessage($message)
{
    echo $message . "\n";

    $logMessage = "[".date('Y-m-d H:i:s')."] - {$message}\n";
    // write to log file
}