<?php

use function lib\arguments\isHelpRequested;
use function lib\arguments\isVersionRequested;
use function lib\arguments\prepareArguments;
use function lib\common\loadScanDir;
use function lib\disk\getFileList;
use function lib\disk\loadFileList;
use function lib\log\loadOrCreateHashingLog;
use function lib\log\logMessage;
use function lib\mhl\loadMhlFiles;
use function lib\mhl\verifyHashes;
use function lib\print\printDirectoryStats;
use function lib\print\printState;
use function lib\print\printUsage;
use function lib\print\printVersion;

require_once('lib/common.php');

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