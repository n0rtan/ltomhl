<?php

use function lib\arguments\isHelpRequested;
use function lib\arguments\isVersionRequested;
use function lib\arguments\prepareArguments;
use function lib\common\loadScanDir;
use function lib\console\consolePrintDirectoryStats;
use function lib\console\consolePrintHelp;
use function lib\console\consolePrintMessage;
use function lib\console\consolePrintState;
use function lib\console\consolePrintVersion;
use function lib\disk\getFileList;
use function lib\disk\loadFileList;
use function lib\log\logClose;
use function lib\log\logMessage;
use function lib\log\logOpen;
use function lib\mhl\loadMhlFiles;
use function lib\mhl\makeMhlFile;
use function lib\mhl\verifyHashes;
use function lib\report\makeReport;
use function progress\progressInit;

require_once('lib/common.php');

try {

    logOpen();

    prepareArguments();

    if (isHelpRequested()) {
        consolePrintHelp();
        exit;
    }

    if (isVersionRequested()) {
        consolePrintVersion();
        exit;
    }

    $stateMsg = consolePrintState();
    logMessage($stateMsg);

    consolePrintMessage('Loading directory contents...');
    logMessage('Loading directory contents');
    loadFileList();

    

    consolePrintMessage('Loading MHL files data...');
    logMessage('Loading MHL files data');
    loadMhlFiles();

    $dirStatsMsg = consolePrintDirectoryStats();
    logMessage($dirStatsMsg);

    consolePrintMessage('Init progress');
    logMessage('Init progress');
    progressInit();

    consolePrintMessage('Verifying...');
    logMessage('Verifying...');
    $processedCount = verifyHashes();

    consolePrintMessage("{$processedCount} files processed.");
    logMessage("{$processedCount} files processed.");

    makeReport();
    makeMhlFile();   

} catch (Exception $exception) {
    echo "\n*** Some error occurs: {$exception->getMessage()}\n";
} finally {
    logClose();
}