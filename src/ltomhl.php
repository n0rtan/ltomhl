<?php

use function lib\arguments\isHelpRequested;
use function lib\arguments\prepareArguments;
use function lib\common\loadScanDir;
use function lib\common\printState;
use function lib\common\printUsage;

require_once('lib/common.php');

try {

    prepareArguments();

    if (isHelpRequested()) {
        printUsage();
        exit;
    }

    loadScanDir();
    printState();

    

} catch (Exception $exception) {
    echo "Some error occurs: {$exception->getMessage()}";
}