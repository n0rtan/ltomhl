<?php

namespace lib\arguments;

use Exception;

/**
 * arguments list:
 * -sd <scan_dir_path> - scan dir path full
 * -mhl <mhl1_full_path> <mhl2_full_path> ... - mhl files
 */

require_once('errors.php');

define('ARG_KEY_HELP', 'help');
define('ARG_KEY_SCAN_DIRECTORY', 'scandir');
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
    global $arguments;

    $isHelpPassed = isset($arguments[ARG_KEY_HELP]);
    $isScanDirectoryPassed = !empty($arguments[ARG_KEY_SCAN_DIRECTORY]);
    $isMhlFilesPassed = !empty($arguments[ARG_KEY_MHL_FILES]);
    
    if (!$isScanDirectoryPassed && !$isMhlFilesPassed && !$isHelpPassed) {
        throw new Exception(
            "\n\tNeither the scan dir nor the mhl files is specified.\n" .
            "\tPassed arguments list: \n" . print_r($arguments, true)
        );
    }

    $isMhlFilesMoreThanOne = count($arguments[ARG_KEY_MHL_FILES]) > 1;

    if ($isMhlFilesMoreThanOne && !$isScanDirectoryPassed) {
        throw new Exception(
            "\n\tWe have several mhl files and empty scan directory.\n" . 
            "\tPlease set scan directory or select single mhl file.\n" . 
            "\tPassed arguments list: \n" . print_r($arguments, true)
        );
    }

}

function getMhlFilePaths(): array
{
    global $arguments;

    if (empty($arguments[ARG_KEY_MHL_FILES])) {
        throw new Exception('Any MHL is not specified', ERROR_SCAN_DIR_NOT_SPECIFIED);
    }

    return $arguments[ARG_KEY_MHL_FILES];
}

function getScanDir(): string
{
    global $arguments;

    if (
        empty($arguments[ARG_KEY_SCAN_DIRECTORY][0]) ||
        !is_dir($arguments[ARG_KEY_SCAN_DIRECTORY][0])
       ) {
        throw new Exception(
            "Scan dir is not specified",
            ERROR_SCAN_DIR_NOT_SPECIFIED
        );
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