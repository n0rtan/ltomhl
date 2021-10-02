<?php

namespace lib\log;

use function lib\mhl\getNewFileBaseName;

$hLogFile = null;
$logFileName = 'main.log';

function getLogFilePath()
{
    return getcwd() . DIRECTORY_SEPARATOR . getNewFileBaseName() . '.log';
}

/**
 * Opens log file. Need to use logClose() at the end of programm.
 */
function logOpen(): void
{
    global $hLogFile, $logFileName;
    
    $hLogFile = fopen($logFileName, 'w');
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