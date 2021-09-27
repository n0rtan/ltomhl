<?php

use function lib\arguments\getMhlFilePaths;
use function lib\common\getScanDir;

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

    $mhlFileDirName = basename(dirname($mhlFileAbsolutePath));
    $filePathFromMhl = normalizePath($data->file->__toString());

    $relativeFilePath = $mhlFileDirName . DIRECTORY_SEPARATOR . $filePathFromMhl;

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
            throw new Exception("Error in {$mhlFileAbsolutePath}: {$exception->getMessage()}");
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

    $lastHashedFile = getLastHashedFile();
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
        
        $fileAbsolutePath = $scanDir . DIRECTORY_SEPARATOR . $filePath;
        $isNonInMhl = false;
        try {
            list($hashType, $savedFromMhlHash) = chooseHash((object)$fileData);
        } catch(Exception $exception) {
            if ($exception->getCode() !== ERROR_NO_HASH_FOUND_IN_MHL) {
                throw $exception;
            }
            $isNonInMhl = true;
        }

        logProgress($filePath);

        $calculatedHash = calcHash($fileAbsolutePath, $hashType ?? $hashPriorityList[0]);

        if ($isNonInMhl) {
            logMessage("$filePath not exists in mhl file. Calculated hash is {$calculatedHash}");
            continue;
        }        

        if ($savedFromMhlHash !== $calculatedHash) {
            logMessage("Bad hash for file: $filePath");
        } else {
            logMessage("$filePath OK!");
        }

        $filesProcessed++;
    }

    return $filesProcessed;
}