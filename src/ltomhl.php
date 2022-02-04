<?php

use function lib\arguments\getLocalDir;
use function lib\arguments\isHelpRequested;
use function lib\arguments\isVersionRequested;
use function lib\arguments\prepareArguments;
use function lib\console\consolePrintDirectoryStats;
use function lib\console\consolePrintHelp;
use function lib\console\consolePrintMessage;
use function lib\console\consolePrintState;
use function lib\console\consolePrintVersion;
use function lib\disk\findScanFolders;
use function lib\disk\restoreFileList;
use function lib\disk\readFileList;
use function lib\disk\saveFileList;
use function lib\log\logClose;
use function lib\log\logMessage;
use function lib\log\logOpen;
use function lib\log_bad\logBadClose;
use function lib\log_bad\logBadOpen;
use function lib\mhl\loadMhlFiles;
use function lib\mhl\makeMhlFile;
use function lib\mhl\verifyHashes;
use function lib\report\getInvalidFilesCount;
use function lib\report\makeReport;
use function lib\progress\progressClose;
use function lib\progress\progressInit;

require_once('lib/common.php');

try {

    ini_set('memory_limit', '2047M');

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

    logBadOpen();

    $stateMsg = consolePrintState();
    logMessage($stateMsg);

    consolePrintMessage('Loading file list...');
    logMessage('Loading file list');

    findScanFolders(getLocalDir());

    if (!restoreFileList()) {
        consolePrintMessage('Reading directory contents...');
        logMessage('Reading directory contents');
        readFileList();

        consolePrintMessage('Loading MHL files data...');
        logMessage('Loading MHL files data');
        loadMhlFiles();
        saveFileList();
    }

    $dirStatsMsg = consolePrintDirectoryStats();
    logMessage($dirStatsMsg);

    progressInit();

    consolePrintMessage('Verifying...');
    logMessage('Verifying...');
    $processedCount = verifyHashes();

    consolePrintMessage("{$processedCount} files processed. " . getInvalidFilesCount() . ' files invalid.');
    logMessage("{$processedCount} files processed. " . getInvalidFilesCount() . ' files are invalid.');

    makeReport();
    makeMhlFile();

} catch (Exception $exception) {
    echo "\n*** Some error occurs: {$exception->getMessage()}\n";
} finally {
    logClose();
    logBadClose();
    progressClose();
}