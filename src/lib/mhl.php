<?php

use function lib\arguments\getMhlFilePaths;

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
            if ($exception->getCode() === ERROR_FILE_NOT_FOUNT_ON_STORAGE) {
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
            ERROR_FILE_NOT_FOUNT_ON_STORAGE
        );
    }

    try {
        $fileList[$relativeFilePath]['mhl_file'] = $mhlFileAbsolutePath;

        list($hashType, $hashValue) = chooseHash($data);
        $fileList[$relativeFilePath][$hashType] = $hashValue;
    } catch(Exception $exception) {

        if ($exception->getCode() === ERROR_NO_HASH_FOUNT_IN_MHL) {
            throw new Exception("Error in {$mhlFileAbsolutePath}: {$exception->getMessage()}");
        }

        throw $exception;
    }
}

function chooseHash($data): array
{
    global $hashPriorityList;

    foreach($hashPriorityList as $needed) {

        if (!empty($data->{$needed})) {
            return [
                $needed,
                $data->{$needed}->__toString(),
            ];
        }
    }

    throw new Exception("No hash found for {$data->file->__toString()}", ERROR_NO_HASH_FOUNT_IN_MHL);
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

function calcHash($file_path): string
{
    exec("mhl hash -t md5 {$file_path}", $output);

    if (count($output) > 1) {
        throw new Exception("Invalid mhl hash output: \n" . implode("\n", $output));
    }

    return $output[0];
}