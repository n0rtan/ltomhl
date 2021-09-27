<?php

namespace lib\print;

use function lib\disk\getFileList;

function printUsage()
{
    global $version;

    echo "LTO-MHL version: {$version}\n";
    echo "Usage: \n";
    echo "\t-help - show this help\n";
    echo "\t-version - show version\n";
    echo "\t-reset - reset progress\n";
    echo "\t-mhl <path_to_mhl_file1> ... <path_to_mhl_fileN> - MHL-files\n";
    echo "\t-scandir <path_ro_scan_dir> - path to directory with files for scan\n";
    echo "\texample: php ltomhl.php -mhl \"D:\mhl_src\\flash1\\test.mhl\" \"D:\mhl_src\\flash2\\test.mhl\" -scandir \"D:\\mhl_dst\\1231312\"\n";
}

function printVersion()
{
    global $version;

    echo "LTO-MHL version: {$version}\n";
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