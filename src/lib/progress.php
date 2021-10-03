<?php

namespace progress;

use function lib\arguments\isResetRequested;
use function lib\console\consolePrintMessage;
use function lib\log\logMessage;
use function lib\mhl\getNewFileNamePrefix;
use function lib\report\addInvalidFile;
use function lib\report\addNotInMhlFile;
use function lib\report\addVerifiedFile;

$progressLastHashedFile = null;
$progressFileBaseName = 'progress';
$progressFile = null;

function progressGetLastHashedFile(): ?string
{
    global $progressLastHashedFile;

    return $progressLastHashedFile;
}

function getProgressFileName()
{
    global $progressFileBaseName;

    return '.' . getNewFileNamePrefix() . '_' . $progressFileBaseName;
}

function getProgressfilePath()
{
    return getcwd() . DIRECTORY_SEPARATOR . getProgressFileName();
}

function progressAdd($filePath, $result): void
{ 
    global $progressFile;

    $json = json_encode([
        'file' => $filePath,
        'result' => $result,
    ]);

    fwrite($progressFile, $json . PHP_EOL);    
}

function progressOpen($clean = false)
{
    global $progressFile;

    $progressFile = fopen(getProgressfilePath(), $clean ? 'w+' : 'a+');
}

function progressClose()
{
    global $progressFile;

    fclose($progressFile);
}

function progressInit(): void
{
    global $progressLastHashedFile, $progressFile;

    progressOpen(isResetRequested());

    $lastFile = null;

    $fileInfo = fstat($progressFile);

    if (!empty($fileInfo['size'])) {
        consolePrintMessage('Loading progress...');
        logMessage('Loading progress...');
    }

    while (($line = fgets($progressFile)) !== false) {
        $data = json_decode($line, true);
        $lastFile = $data['file'];
        loadProgressByFile($data['result']);
    }

    $progressLastHashedFile = $lastFile;

    if (!empty($progressLastHashedFile)) {
        consolePrintMessage("Last verified file: {$progressLastHashedFile}");
        logMessage("Last verified file: {$progressLastHashedFile}");
    }
}

function loadProgressByFile($result): void
{
    switch($result['type']) {
        case 'not_in_mhl':
            addNotInMhlFile(
                $result['fileAbsolutePath'], 
                $result['validHashType'], 
                $result['validHashValue'], 
                $result['hasdate'],
                $result['size'],
                $result['creationdate'],
                $result['lastmodificationdate']
            );
            break;

        case 'invalid':
            addInvalidFile($result['mhl_file'], $result['fileAbsolutePath'], $result['validHashType'], $result['validHashValue']);
            break;

        case 'valid':
            addVerifiedFile($result['mhl_file'], $result['fileAbsolutePath']);
            break;
    }
}