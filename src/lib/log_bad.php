<?php

namespace lib\log_bad;

use function lib\mhl\getNewFileBaseName;

$hBadLogFile = null;
$logBadFileName = 'BAD';

function getLogFilePath()
{
    global $logBadFileName;

    return getcwd() . DIRECTORY_SEPARATOR . getNewFileBaseName() . "_$logBadFileName.log";
}

/**
 * Opens log file. Need to use logClose() at the end of programm.
 */
function logBadOpen(): void
{
    global $hBadLogFile;
    
    $hBadLogFile = fopen(getLogFilePath(), 'a');
}

/**
 * Closes log file.
 */
function logBadClose(): void
{
    global $hBadLogFile;

    fclose($hBadLogFile);
}

/**
 * Writes log message to file.
 */
function logBadAdd($filePath): void
{
    global $hBadLogFile;

    fwrite($hBadLogFile, $filePath . PHP_EOL);
}