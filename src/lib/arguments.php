<?php

namespace lib\arguments;

use Exception;

require_once('errors.php');

define('ARG_KEY_HELP', 'help');
define('ARG_KEY_VERSION', 'version');
define('ARG_KEY_RESET', 'reset');
define('ARG_KEY_LP', 'lp');
define('ARG_KEY_TP', 'tp');
define('ARG_KEY_MHL_FILES', 'mhl');
define('ARG_KEYS', [
    ARG_KEY_HELP,
    ARG_KEY_RESET,
    ARG_KEY_VERSION,
    ARG_KEY_LP,
    ARG_KEY_TP,
    ARG_KEY_MHL_FILES,
]);
define('ARG_STOP_KEYS', [
    ARG_KEY_HELP,
    ARG_KEY_VERSION,
]);

$arguments = [];

function prepareArguments(): void
{
    readArguments();
    verifyArguments();
}

function verifyArguments(): void
{
    global $arguments;

    if (array_intersect(ARG_STOP_KEYS, array_keys($arguments))) {
        return;
    }

    $isScanDirectoryPassed = !empty($arguments[ARG_KEY_TP]);
    $isLocalDirectoryPassed = !empty($arguments[ARG_KEY_LP]);
    
    if (!$isScanDirectoryPassed) {
        throw new Exception(
            "\n\tTape path is not specified.\n" .
            "\tPassed arguments list: \n" . print_r($arguments, true)
        );
    }

    if (!$isLocalDirectoryPassed) {
        throw new Exception(
            "\n\tLocal path is not specified.\n" .
            "\tPassed arguments list: \n" . print_r($arguments, true)
        );
    }

    $isMhlFilesPassed = !empty($arguments[ARG_KEY_MHL_FILES]);
    
    if (!$isMhlFilesPassed) {
        throw new Exception(
            "\n\tPlease set mhl-files.\n" . 
            "\tPassed arguments list: \n" . print_r($arguments, true)
        );
    }

}

function getMhlFilePaths(): array
{
    global $arguments;

    if (empty($arguments[ARG_KEY_MHL_FILES])) {
        throw new Exception(
            'Any MHL is not specified',
            ERROR_MHIL_FILES_NOT_SPECIFIED
        );
    }

    return $arguments[ARG_KEY_MHL_FILES];
}

function readArguments(): array
{
    global $argv, $arguments;

    array_shift($argv);

    $argType = null;

    foreach($argv as $arg) {
        
        $argVal = ltrim($arg, '-');

        // todo: check is key

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

function getLocalDir(): string
{
    global $arguments;

    if (
        empty($arguments[ARG_KEY_LP][0]) ||
        !is_dir($arguments[ARG_KEY_LP][0])
       ) {
        throw new Exception(
            "Local dir is not specified",
            ERROR_LOCAL_DIR_NOT_SPECIFIED
        );
    }

    return $arguments[ARG_KEY_LP][0];
}

function getScanDir(): string
{
    global $arguments;

    if (
        empty($arguments[ARG_KEY_TP][0]) ||
        !is_dir($arguments[ARG_KEY_TP][0])
       ) {
        throw new Exception(
            "Scan dir (Tape) is not specified",
            ERROR_TAPE_DIR_NOT_SPECIFIED
        );
    }

    return $arguments[ARG_KEY_TP][0];
}

function isHelpRequested(): bool
{
    global $arguments;

    return isset($arguments[ARG_KEY_HELP]);
}

function isVersionRequested(): bool
{
    global $arguments;

    return isset($arguments[ARG_KEY_VERSION]);
}

function isResetRequested(): bool
{
    global $arguments;

    return isset($arguments[ARG_KEY_RESET]);
}