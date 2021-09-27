<?php

namespace lib\console;

function consolePrintMessage($message, $newLine = true)
{
    echo $message . ($newLine ? "\n" : '');
}

use function lib\disk\getFileList;

function consolePrintHelp()
{
    global $version;

    $str = "LTO-MHL version: {$version}\n".
    "Usage: \n".
    "\t-help - show this help\n".
    "\t-version - show version\n".
    "\t-reset - reset progress\n".
    "\t-mhl <path_to_mhl_file1> ... <path_to_mhl_fileN> - MHL-files\n".
    "\t-scandir <path_ro_scan_dir> - path to directory with files for scan\n".
    "\texample: php ltomhl.php -mhl \"D:\mhl_src\\flash1\\test.mhl\" \"D:\mhl_src\\flash2\\test.mhl\" -scandir \"D:\\mhl_dst\\1231312\"\n";
    consolePrintMessage($str);
}

function consolePrintVersion()
{
    global $version;

    $str = "LTO-MHL version: {$version}\n";

    consolePrintMessage($str);
}

function consolePrintState()
{
    global $scanDir;

    $str = "----------------------\n".
    "Current directory: " . getcwd() . "\n".
    "Scan dir: {$scanDir}\n" .
    "----------------------\n";
    consolePrintMessage($str);

    return $str;
}

function consolePrintDirectoryStats()
{
    $fileList = getFileList();

    $str = "\n----------------------\n".
    "Files in list: " . count($fileList) . "\n".
    "----------------------\n";

    consolePrintMessage($str);
    
    return $str;
}