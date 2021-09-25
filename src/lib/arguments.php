<?php

namespace lib\arguments;

use Exception;

/**
 * arguments list:
 * -sd <scan_dir_path> - scan dir path full
 * -mhl <mhl1_full_path> <mhl2_full_path> ... - mhl files
 */

require_once('errors.php');

define('ARG_KEY_HELP', 'h');
define('ARG_KEY_SCAN_DIRECTORY', 'sd');
define('ARG_KEY_MHL_FILES', 'mhl');
define('ARG_KEYS', [
    ARG_KEY_HELP,
    ARG_KEY_SCAN_DIRECTORY,
    ARG_KEY_MHL_FILES,
]);

$arguments = [];

function prepareArguments()
{
    readArguments();
    verifyArguments();
}

function verifyArguments(): void
{
    global $argv, $arguments;

    $isHelpPassed = isset($arguments[ARG_KEY_HELP]);
    $isScanDirectoryPassed = !empty($arguments[ARG_KEY_SCAN_DIRECTORY]);
    $isMhlFilesPassed = !empty($arguments[ARG_KEY_MHL_FILES]);

    if (!$isScanDirectoryPassed && !$isMhlFilesPassed && !$isHelpPassed) {
        throw new Exception(
            "Neither the scan dir nor the mhl files is specified. Passed arguments list: \n" . print_r($arguments, true)
        );
    }
}

function getMhlFilePath(): string
{
    global $arguments;

    if (empty($arguments[ARG_KEY_MHL_FILES][0])) {
        throw new Exception('Any MHL is not specified', ERROR_SCAN_DIR_NOT_SPECIFIED);
    }

    return $arguments[ARG_KEY_MHL_FILES][0];
}

function getScanDir(): string
{
    global $arguments;

    if (empty($arguments[ARG_KEY_SCAN_DIRECTORY][0]) || !is_dir($arguments[ARG_KEY_SCAN_DIRECTORY][0])) {
        throw new Exception("Scan dir is not specified", ERROR_SCAN_DIR_NOT_SPECIFIED);
    }

    return $arguments[ARG_KEY_SCAN_DIRECTORY][0];
}

function readArguments()
{
    global $argv, $arguments;

    array_shift($argv);

    $argType = null;

    foreach($argv as $arg) {
        
        $argVal = ltrim($arg, '-');

        if (in_array($argVal, ARG_KEYS)) {
            $argType = $argVal;
            $arguments[$argType] = [];
            continue;
        } 
            
        if (empty($argType)) {
            throw new Exception(
                "Invalid arguments: " . var_export($argv, true),
                ERROR_INVALID_ARGUMENTS
            );
        }

        $arguments[$argType][] = $arg;
    }

    return $arguments;
}

function isHelpRequested()
{
    global $arguments;

    return isset($arguments[ARG_KEY_HELP]);
}