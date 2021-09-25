<?php

use function lib\arguments\isHelpRequested;
use function lib\arguments\prepareArguments;
use function lib\common\loadScanDir;
use function lib\common\printState;
use function lib\common\printUsage;

require_once('lib/common.php');
require_once('lib/disk.php');

try {

    prepareArguments();

    if (isHelpRequested()) {
        printUsage();
        exit;
    }

    loadScanDir();
    printState();

    // load File List
    loadFileList();
    echo var_export(getFileList(), true);

    // load mhl

    // load/create log

    // verify hashes

    // make report

} catch (Exception $exception) {
    echo "Some error occurs: {$exception->getMessage()}";
}