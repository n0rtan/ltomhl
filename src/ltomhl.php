<?php

use function lib\arguments\isHelpRequested;
use function lib\arguments\isVersionRequested;
use function lib\arguments\prepareArguments;
use function lib\common\loadScanDir;
use function lib\common\printDirectoryStats;
use function lib\common\printState;
use function lib\common\printUsage;
use function lib\common\printVersion;

require_once('lib/common.php');
require_once('lib/disk.php');
require_once('lib/mhl.php');

try {

    prepareArguments();

    if (isHelpRequested()) {
        printUsage();
        exit;
    }

    if (isVersionRequested()) {
        printVersion();
        exit;
    }

    loadScanDir();
    printState();

    logMessage('Loading directory contents');
    loadFileList();

    logMessage('Loading MHL files data');
    loadMhlFiles();

    printDirectoryStats();

    logMessage('Init progress');
    loadOrCreateHashingLog();

    logMessage('Verifying');
    $processedCount = verifyHashes();

    logMessage("{$processedCount} files processed.");

    // make report

} catch (Exception $exception) {
    echo "*** Some error occurs: {$exception->getMessage()}\n";
}