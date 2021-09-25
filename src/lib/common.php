<?php

namespace lib\common;

use Exception;

use function lib\arguments\getMhlFilePath;
use function lib\arguments\getScanDir as ArgumentsGetScanDir;

require_once('arguments.php');
require_once('disk.php');

$scanDir = '';

function loadScanDir(): string
{
    global $scanDir;

    try {
        $scanDir = ArgumentsGetScanDir();
    } catch (Exception $exception) {

        if (!in_array($exception->getCode(), ERRORS_ALL)) {
            throw $exception;
        }

        $scanDir = dirname(getMhlFilePath());   
    }
    
    return $scanDir;
}

function printUsage()
{
    echo "Usage: ";
}

function printState()
{
    global $scanDir;

    echo "Current directory: " . getcwd() . "\n";
    echo "Scan dir: {$scanDir}\n";
}