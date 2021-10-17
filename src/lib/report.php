<?php

namespace lib\report;

use function lib\arguments\getMhlFilePaths;
use function lib\arguments\getScanDir;
use function lib\disk\getScanFolders;
use function lib\mhl\getNewFileNamePrefix;
use function lib\mhl\getStartTimeFormatted;

$filesNotInMhl = [];

$filesProcessed = [];
$invalidFilesCount = 0;
$validFilesCount = 0;

$startTime = time();

function getInvalidFilesCount()
{
    global $invalidFilesCount;

    return $invalidFilesCount;
}

function getValidFilesCount()
{
    global $validFilesCount;

    return $validFilesCount;
}

function addNotInMhlFile($filePath, $hashType, $hashVal, $hashdate, $size = null, $creationdate = null, $lastmodificationdate = null): void
{
    global $filesNotInMhl;

    $filesNotInMhl[$filePath] = [
        $hashType => $hashVal,
        'hashdate' => $hashdate,
    ];

    if (!is_null($size)) {
        $filesNotInMhl[$filePath][] = $size;
    }

    if (!is_null($creationdate)) {
        $filesNotInMhl[$filePath]['creationdate'] = date('Y-m-d H:i:s T', $creationdate);
    }

    if (!is_null($lastmodificationdate)) {
        $filesNotInMhl[$filePath]['lastmodificationdate'] = date('Y-m-d H:i:s T', $lastmodificationdate);
    }
}

function addVerifiedFile($mhl, $filePath): void
{
    global $filesProcessed, $validFilesCount;

    $filesProcessed[$mhl]['valid'][$filePath] = [];
    $validFilesCount = count($filesProcessed[$mhl]['valid']);
}

function addInvalidFile($mhl, $filePath, $validHashType, $validHashValue): void
{
    global $filesProcessed, $invalidFilesCount;

    $filesProcessed[$mhl]['invalid'][$filePath] = [
        'validHashType' => $validHashType,
        'validHashValue' => $validHashValue,
    ];

    $invalidFilesCount = count($filesProcessed[$mhl]['invalid']);
}

function getReportFilePath()
{
    return getcwd() . DIRECTORY_SEPARATOR . getReportFileBaseName();
}

function getReportFileBaseName()
{
    return getNewFileNamePrefix() . "_" . getStartTimeFormatted() . ".html";
}

function cleanPathForReport($filePath)
{
    return ltrim(str_replace(getScanDir(), '', $filePath), '/\\');
}

function makeReport()
{
    global $filesProcessed;

    $hFile = fopen(getReportFilePath(), 'w');

    $dayTitle = basename(getScanDir());

    fwrite($hFile, "<html>
    <head>
        <style>

            body {
                background-color: #181818;
                font-size: 1em;
                font-family: monospace;
                color: #ababab;
                margin: 5px 3px;
            }

            .titul
            {
                text-align: center;
            }

            .caption {
                font-size: 1.6em;
                font-weight: bold;
                background-color: hsl(0deg 0% 20%);
                border-radius: 4px;
                color: #d9d9d9;
                padding: 1px 5px;
                text-shadow: 1px 1px 6px #000000;
                margin-top: 10px !important;
            }

            .line {
                padding: 2px 2px 2px 5px;
                font-size: 1.4em;
            }

            .invalid {
                color: red;
            }


            .collapsible {
                color: white;
                cursor: pointer;
                border: none;
                text-align: left;
                outline: none;
                margin: 3px 1px;
              }
              
              .content {
                display: block;
                overflow: hidden;
              }

        </style>

        <title>DAY {$dayTitle} report</title>
    </head>
    <body>\n");


    fwrite($hFile, "\n<h1 class='titul'>DAY {$dayTitle}</h1>\n");

    // folders

    fwrite($hFile, "\n<div>
        <div class='caption'>Folders:</div>");

    foreach(getScanFolders() as $scanFolder) {
        fwrite($hFile, "\n<div class='line'>{$scanFolder}</div>\n");
    }

    fwrite($hFile, "\n</div>\n");

    // /folders

    // MHLs

    fwrite($hFile, "\n<div>
        <div class='caption'>MHLs:</div>");

    foreach(getMhlFilePaths() as $mhlPath) {
        $mhlFileName = basename($mhlPath);
        fwrite($hFile, "\n<div class='line'>{$mhlFileName}</div>\n");
    }

    fwrite($hFile, "\n</div>\n");

    // /MHLs

    // Summary

    $validCount   = array_reduce($filesProcessed, fn($carry, $item) => $carry += count($item['valid']) ?? 0, 0);
    $invalidCount = array_reduce($filesProcessed, fn($carry, $item) => $carry += count($item['invalid']) ?? 0, 0);
    $filesTotal = $validCount + $invalidCount;

    fwrite($hFile, "\n<div>
        <div class='caption'>Summary:</div>");
        fwrite($hFile, "\n<div class='line'>{$filesTotal} files processed. {$invalidCount} files invalid</div>\n");
    fwrite($hFile, "\n</div>\n");

    // /Summary


    fwrite($hFile, "\n<div class='caption collapsible active' type='button'>Invalid files ({$invalidCount}):</div>\n<div class='content'>\n");

    array_walk($filesProcessed, function($item) use ($hFile) {
        if (isset($item['invalid'])) {
            foreach($item['invalid'] as $filePath => $fileData) {
                $cleanPath = cleanPathForReport($filePath);
                fwrite($hFile, "<div class='line invalid'>{$cleanPath} / valid data: {$fileData['validHashType']}:{$fileData['validHashValue']}</div>\n");
            }
        }
    });

    fwrite($hFile, "\n</div>\n<div class='caption collapsible active' type='button'>Valid files ({$validCount}):</div>\n<div class='content'>");

    array_walk($filesProcessed, function($item) use ($hFile) {
        if (isset($item['valid'])) {
            foreach($item['valid'] as $filePath => $fileData) {
                $cleanPath = cleanPathForReport($filePath);
                fwrite($hFile, "<div class='line'>{$cleanPath}</div>\n");
            }
        }  
    });     

    fwrite($hFile, "\n</div>\n " . 
    '<script>
    var coll = document.getElementsByClassName("collapsible");
    var i;
    
    for (i = 0; i < coll.length; i++) {
      coll[i].addEventListener("click", function() {
        var content = this.nextElementSibling;
        if (content.style.display === "none") {
          content.style.display = "block";
        } else {
          content.style.display = "none";
        }
      });
    }
    </script>
    </body>
    </html>');

    fclose($hFile);
}

