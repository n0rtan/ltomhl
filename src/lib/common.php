<?php

namespace lib\common;

use Exception;

use function lib\arguments\getMhlFilePaths;
use function lib\arguments\getScanDir as ArgumentsGetScanDir;

require_once('arguments.php');
require_once('disk.php');
require_once('log.php');
require_once('console.php');
require_once('report.php');

$scanDir = '';

function getScanDir()
{
    global $scanDir;

    return $scanDir;
}

function loadScanDir(): string
{
    global $scanDir;

    try {
        $scanDir = ArgumentsGetScanDir();
    } catch (Exception $exception) {

        if (!in_array($exception->getCode(), ERRORS_ALL)) {
            throw $exception;
        }

        $scanDir = dirname(getMhlFilePaths()[0]);   
    }
    
    return $scanDir;
}

function printUsage()
{
    echo "Usage: \n";
    echo "\t-h show this help\n";
    echo "\t-mhl <path_to_mhl_file1> ... <path_to_mhl_fileN> - MHL-files\n";
    echo "\t-scandir <path_ro_scan_dir> - path to directory with files for scan\n";
}

function printState()
{
    global $scanDir;

    echo "----------------------\n";
    echo "Current directory: " . getcwd() . "\n";
    echo "Scan dir: {$scanDir}\n";
    echo "----------------------\n\n";
}

function printDirectoryStats()
{
    $fileList = getFileList();

    echo "----------------------\n";
    echo "Files in list: " . count($fileList) . "\n";
    echo "----------------------\n";
}