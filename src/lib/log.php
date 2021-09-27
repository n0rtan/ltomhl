<?php

namespace lib\log;

$hLogFile = null;
$logFileName = 'ltomhl.log';

function logOpen()
{
    global $hLogFile, $logFileName;
    
    $currentDir = getcwd();
    $logFilePath = $currentDir . DIRECTORY_SEPARATOR . $logFileName;
    $hLogFile = fopen($logFilePath, 'w');
}

function logClose()
{
    global $hLogFile;

    fclose($hLogFile);
}

function logMessage($message)
{
    global $hLogFile;

    $logMessage = "[".date('Y-m-d H:i:s')."] - {$message}\n";
    
    fwrite($hLogFile, $logMessage);
}