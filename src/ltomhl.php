<?php

use function lib\arguments\isHelpRequested;
use function lib\arguments\prepareArguments;
use function lib\common\loadScanDir;
use function lib\common\printState;
use function lib\common\printUsage;

require_once('lib/common.php');
require_once('lib/disk.php');
require_once('lib/mhl.php');

try {

    prepareArguments();

    if (isHelpRequested()) {
        printUsage();
        exit;
    }

    loadScanDir();
    printState();

    logMessage('Loading directory contents');
    loadFileList();

    logMessage('Loading MHL files data');
    loadMhlFiles();

    echo var_export(getFileList(), true)."\n";

    logMessage('Init progress');
    loadOrCreateHashingLog();

    logMessage('Verifying');
    verifyHashes();

    // make report

} catch (Exception $exception) {
    echo "*** Some error occurs: {$exception->getMessage()}\n";
}