<?php

namespace lib\mhl;

use Exception;
use SimpleXMLElement;

use function lib\arguments\getMhlFilePaths;
use function lib\common\getScanDir;
use function lib\console\consolePrintMessage;
use function lib\disk\getFileList;
use function lib\log\logMessage;
use function progress\progressAdd;
use function progress\progressGetLastHashedFile;

$hashPriorityList = [
    'xxhash64be',
    'xxhash64',
    'xxhash',
    'md5',
    'sha1',
];

function loadMhlFiles()
{
    $files = getMhlFilePaths();

    $isFileNotFoundExists = false;

    foreach($files as $fileAbsolutePath) {
        if (parseMhl($fileAbsolutePath)) {
            $isFileNotFoundExists = true;
        }
    }

    if ($isFileNotFoundExists) {
        throw new Exception("Some files not found on storage. See log.");
    }
}

function parseMhl($fileAbsolutePath): bool
{
    $hashlist = new SimpleXMLElement(file_get_contents($fileAbsolutePath));
    
    $isFileNotFoundExists = false;

    foreach($hashlist->hash as $data) {
        try {
            updateFileList($fileAbsolutePath, $data);
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

function updateFileList($mhlFileAbsolutePath, $data)
{
    global $fileList;

    $relativeFilePath = normalizePath($data->file->__toString());
    
    if (count(getMhlFilePaths()) > 1) {
        $mhlFileDirName = basename(dirname($mhlFileAbsolutePath));
        $relativeFilePath = $mhlFileDirName . DIRECTORY_SEPARATOR . $relativeFilePath;
    }

    if (!isset($fileList[$relativeFilePath])) {
        throw new Exception(
            "File {$relativeFilePath} not found on storage",
            ERROR_FILE_NOT_FOUND_ON_STORAGE
        );
    }

    try {
        $fileList[$relativeFilePath]['mhl_file'] = $mhlFileAbsolutePath;

        list($hashType, $hashValue) = chooseHash($data);
        $fileList[$relativeFilePath][$hashType] = $hashValue;
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

function normalizePath($path)
{
    $normalized = implode(
        DIRECTORY_SEPARATOR,
        explode('/', $path)
    );

    return implode(
        DIRECTORY_SEPARATOR,
        explode('\\', $normalized)
    );    
}

function calcHash($filePath, $hashType): string
{
    ob_start();
    exec("mhl.exe hash -t {$hashType} {$filePath} 2>&1", $output);
    ob_end_clean();

    if (count($output) !== 1) {
        throw new Exception(
            "Invalid mhl hash output: \n" . implode("\n", $output),
            ERROR_INVALID_MHL_HASHING_OUTPUT
        );
    }

    return trim(explode('=', $output[0])[1]);
}

function verifyHashes(): int
{
    global $hashPriorityList;

    $filesProcessed = 0;
    $fileList = getFileList();
    $scanDir = getScanDir();

    $lastHashedFile = progressGetLastHashedFile();
    $paused = !empty($lastHashedFile);
        
    foreach($fileList as $filePath => $fileData) {

        if ($paused) {
            if ($lastHashedFile !== $filePath) {
                continue;
            } else {
                $paused = false;
                continue;
            }        
        }

        consolePrintMessage(
            "$filePath [in progress...] ", false
        );
        
        $fileAbsolutePath = $scanDir . DIRECTORY_SEPARATOR . $filePath;
        $isNotInMhl = false;
        try {
            list($hashType, $hashSavedFromMhl) = chooseHash((object)$fileData);
        } catch(Exception $exception) {
            if ($exception->getCode() !== ERROR_NO_HASH_FOUND_IN_MHL) {
                throw $exception;
            }
            $isNotInMhl = true;
        }

        $calculatedHash = calcHash($fileAbsolutePath, $hashType ?? $hashPriorityList[0]);

        if ($isNotInMhl) {
            consolePrintMessage(
                "not exists in mhl file. Calculated hash is {$calculatedHash}"
            );
            logMessage(
                "$filePath not exists in mhl file. Calculated hash is {$calculatedHash}"
            );
        } else if ($hashSavedFromMhl !== $calculatedHash) {
            consolePrintMessage(
                "bad hash; calculated: {$calculatedHash}"
            );
            logMessage(
                "Bad hash for file: $filePath; calculated: {$calculatedHash}"
            );
        } else {
            consolePrintMessage(
                "OK!"
            );
            logMessage(
                "$filePath OK!"
            );
        }

        $filesProcessed++;

        progressAdd($filePath);
    }

    return $filesProcessed;
}