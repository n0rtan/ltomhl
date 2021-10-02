<?php

namespace lib\mhl;

use Exception;
use SimpleXMLElement;

use function lib\arguments\getLocalDir;
use function lib\arguments\getMhlFilePaths;
use function lib\arguments\getScanDir;
use function lib\console\consolePrintMessage;
use function lib\disk\getFileList;
use function lib\log\logMessage;
use function lib\report\addInvalidFile;
use function lib\report\addNotInMhlFile;
use function lib\report\addVerifiedFile;
use function lib\report\getInvalidFilesCount;
use function progress\progressAdd;
use function progress\progressGetLastHashedFile;

$hashPriorityList = [
    'xxhash64be',
    'xxhash64',
    'xxhash',
    'md5',
    'sha1',
];

function loadMhlFiles(): void
{
    $files = getMhlFilePaths();

    $isFileNotFoundExists = false;

    foreach($files as $mhlFileAbsolutePath) {
        if (parseMhl($mhlFileAbsolutePath)) {
            $isFileNotFoundExists = true;
        }
    }

    if ($isFileNotFoundExists) {
        throw new Exception("Some files not found on storage. See log.");
    }
}

function parseMhl($mhlFileAbsolutePath): bool
{
    $hashlist = new SimpleXMLElement(file_get_contents($mhlFileAbsolutePath));
    
    $isFileNotFoundExists = false;

    foreach($hashlist->hash as $data) {
        try {
            updateFileList($mhlFileAbsolutePath, $data);
        } catch(Exception $exception) {
            consolePrintMessage($exception->getMessage());
            logMessage($exception->getMessage());
            if ($exception->getCode() === ERROR_FILE_NOT_FOUND_ON_STORAGE) {
                $isFileNotFoundExists = true;
            }
        }
    }

    return $isFileNotFoundExists;
}

function updateFileList($mhlFileAbsolutePath, $data): void
{
    global $fileList;

    if (strpos($mhlFileAbsolutePath, getLocalDir()) === false) { // todo: move to varifyArgs
        throw new Exception(
            "File {$mhlFileAbsolutePath} does not contain local path",
            ERROR_INVALID_MHL_PATH
        );
    }

    $fileRelativePath = normalizePath($data->file->__toString());

    $localToScanPath = getScanDir() .  normalizePath(dirname(str_replace(getLocalDir(), '', $mhlFileAbsolutePath)));

    $index = $localToScanPath . DIRECTORY_SEPARATOR . $fileRelativePath;

    if (!isset($fileList[$index])) {
        throw new Exception(
            "File {$index} not found on storage",
            ERROR_FILE_NOT_FOUND_ON_STORAGE
        );
    }

    try {
        $fileList[$index]['mhl_file'] = $mhlFileAbsolutePath;

        list($hashType, $hashValue) = chooseHash($data);
        $fileList[$index][$hashType] = $hashValue;
    } catch(Exception $exception) {

        if ($exception->getCode() === ERROR_NO_HASH_FOUND_IN_MHL) {
            throw new Exception(
                "Error in {$mhlFileAbsolutePath}: {$exception->getMessage()}"
            );
        }

        throw $exception;
    }
}

function chooseHash($data): array
{
    global $hashPriorityList;

    foreach($hashPriorityList as $hashType) {

        if (!empty($data->{$hashType})) {
            return [
                $hashType,
                $data->{$hashType} . '',
            ];
        }
    }

    throw new Exception("No hash found.", ERROR_NO_HASH_FOUND_IN_MHL);
}

function normalizePath($path): string
{
    return rtrim(implode(
        DIRECTORY_SEPARATOR,
        explode('\\', implode(
            DIRECTORY_SEPARATOR,
            explode('/', $path)
        ))
    ), DIRECTORY_SEPARATOR);    
}

function calcHash($filePath, $hashType): string
{
    ob_start();
    exec("mhl hash -t {$hashType} \"{$filePath}\" 2>&1", $output);
    ob_end_clean();

    if (count($output) !== 1 || strpos($output[0], 'sh:') !== false) {
        throw new Exception(
            "Invalid mhl hash output: \n\t" . implode("\n", $output),
            ERROR_INVALID_MHL_HASHING_OUTPUT
        );
    }

    return trim(explode('=', $output[0])[1]);
}

function verifyHashes(): int
{
    global $hashPriorityList, $filesCount;

    $filesProcessed = 0;
    $fileList = getFileList();

    $lastHashedFile = progressGetLastHashedFile();
    $paused = !empty($lastHashedFile);
    
    $i = 1;

    foreach($fileList as $fileAbsolutePath => $fileData) {

        if ($paused) {
            if ($lastHashedFile !== $fileAbsolutePath) {
                continue;
            } else {
                $paused = false;
                continue;
            }        
        }
        
        $invalidFilesCountString = getInvalidFilesCount() ? '(' . getInvalidFilesCount() . ')' : '';

        consolePrintMessage(
            "{$i}{$invalidFilesCountString}/$filesCount: $fileAbsolutePath [in progress...] ", false
        );
        
        $isNotInMhl = false;

        try {
            list($hashType, $hashSavedFromMhl) = chooseHash((object)$fileData);
        } catch(Exception $exception) {
            if ($exception->getCode() !== ERROR_NO_HASH_FOUND_IN_MHL) {
                throw $exception;
            }
            $isNotInMhl = true;
        }

        $hashTypeForCalc = $hashType ?? $hashPriorityList[0];
        $calculatedHash = calcHash($fileAbsolutePath, $hashTypeForCalc);

        if ($isNotInMhl) {
            addNotInMhlFile($fileAbsolutePath, $hashTypeForCalc, $calculatedHash);
            consolePrintMessage(
                "not exists in mhl file. Calculated hash is {$calculatedHash} of type {$hashTypeForCalc}"
            );
            logMessage(
                "$fileAbsolutePath not exists in mhl file. Calculated hash is {$calculatedHash} of type {$hashTypeForCalc}"
            );
        } else if ($hashSavedFromMhl !== $calculatedHash) {
            addInvalidFile(basename($fileData['mhl_file']), $fileAbsolutePath);
            consolePrintMessage(
                "bad hash; calculated: {$calculatedHash}"
            );
            logMessage(
                "Bad hash for file: $fileAbsolutePath; calculated: {$calculatedHash}"
            );
        } else {
            addVerifiedFile(basename($fileData['mhl_file']), $fileAbsolutePath);
            consolePrintMessage(
                "OK!"
            );
            logMessage(
                "$fileAbsolutePath OK!"
            );
        }

        $filesProcessed++;
        $i++;

        progressAdd($fileAbsolutePath);
    }

    return $filesProcessed;
}

function makeMhlFile()
{
    global $filesNotInMhl, $startTime;

    $startDateTime = date('YmdHi', $startTime);

    $currentDir = getcwd();
    $reportFilePath = $currentDir . DIRECTORY_SEPARATOR . "nika-{$startDateTime}.mhl";

    $hFile = fopen($reportFilePath, 'w');

    fwrite($hFile, '<?xml version="1.0" encoding="UTF-8"?>
  <hashlist version="1.1">
    <creatorinfo>
      <username>Nika Digital</username>
      <hostname>'. gethostname() .'</hostname>
      <tool>mhl ver. 0.2.0</tool>
      <startdate>'. date('Y-m-d H:i:s T', $startTime) .'</startdate>
      <finishdate>'. date('Y-m-d H:i:s T', time()) .'</finishdate>
    </creatorinfo>
    ');

    foreach($filesNotInMhl as $filePath => $fileData) {
        fwrite($hFile, "\n    <hash><file>{$filePath}</file></hash>");
    }

    fwrite($hFile, "\n</hashlist>");

    fclose($hFile);
}