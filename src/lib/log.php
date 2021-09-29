<?php

namespace lib\log;

$hLogFile = null;
$logFileName = 'ltomhl.log';

/**
 * Opens log file. Need to use logClose() at the end of programm.
 */
function logOpen(): void
{
    global $hLogFile, $logFileName;
    
    $currentDir = getcwd();
    $logFilePath = $currentDir . DIRECTORY_SEPARATOR . $logFileName;
    $hLogFile = fopen($logFilePath, 'w');
}

/**
 * Closes log file.
 */
function logClose(): void
{
    global $hLogFile;

    fclose($hLogFile);
}

/**
 * Writes log message to file.
 */
function logMessage($message): void
{
    global $hLogFile;

    $logMessage = "[".date('Y-m-d H:i:s')."] - {$message}\n";
    
    fwrite($hLogFile, $logMessage);
}