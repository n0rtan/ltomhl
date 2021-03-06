<?php

namespace lib\console;

function consolePrintMessage($message, $newLine = true): void
{
    echo $message . ($newLine ? "\n" : '');
}

use function lib\disk\getFileList;
use function lib\arguments\getScanDir;

function consolePrintHelp(): void
{
    global $version;

    $str = "LTO-MHL version: {$version}\n".
    "Usage: \n".
    "\t-help - show this help\n".
    "\t-version - show version\n".
    "\t-reset - reset progress\n".
    "\t-mhl <path_to_mhl_file1> ... <path_to_mhl_fileN> - MHL-files\n".
    "\t-scandir <path_ro_scan_dir> - path to directory with files for scan\n".
    "\texample1: php \"d:\\proj\\php\\ltomhl\\src\\ltomhl.php\" -mhl \"e:\\mhl_src\\1231312\\flash1\\test1.mhl\" \"e:\\mhl_src\\1231312\\flash2\\test2.mhl\" -tp \"d:\\mhl_dst\\1231312\" -lp \"e:\\mhl_src\\1231312\" -reset\n".
    "\texample2: php \"d:\\proj\\php\\ltomhl\\src\\ltomhl.php\" -mhl \"e:\\mhl_src\\1231312\\flash1\\test1.mhl\" -tp \"d:\\mhl_dst\\1231312\\flash1\" -lp \"e:\\mhl_src\\1231312\\flash1\" -reset\n";
    consolePrintMessage($str);
}

function consolePrintVersion(): void
{
    global $version;

    $str = "LTO-MHL version: {$version}\n";

    consolePrintMessage($str);
}

function consolePrintState(): string
{
    $scanDir = getScanDir();

    $str = "\n----------------------\n".
    "Current directory: " . getcwd() . "\n".
    "Scan dir: {$scanDir}\n" .
    "----------------------\n";
    consolePrintMessage($str);

    return $str;
}

function consolePrintDirectoryStats(): string
{
    $fileList = getFileList();

    $str = "\n----------------------\n".
    "Files in list: " . count($fileList) . "\n".
    "----------------------\n";

    consolePrintMessage($str);
    
    return $str;
}