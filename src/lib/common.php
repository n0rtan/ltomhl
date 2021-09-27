<?php

namespace lib\common;

use Exception;

use function lib\arguments\getMhlFilePaths;
use function lib\arguments\getScanDir as ArgumentsGetScanDir;

require_once('arguments.php');
require_once('disk.php');
require_once('mhl.php');
require_once('log.php');
require_once('console.php');
require_once('report.php');
require_once('print.php');

$version = '1.0.0';

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