<?php

namespace lib\report;

use function lib\arguments\getScanDir;
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

function makeReport()
{
    global $filesProcessed, $startTime;

    foreach(array_keys($filesProcessed) as $mhlName) {
        makeReportFromExistHhl(getReportFileBaseName($mhlName), $mhlName);
    }
}

function getReportFilePath($reportFileName)
{
    return getcwd() . DIRECTORY_SEPARATOR . $reportFileName;
}

function getReportFileBaseName($mhlName)
{
    return getNewFileNamePrefix() . "_{$mhlName}_" . getStartTimeFormatted() . ".html";
}

function cleanPathForReport($filePath)
{
    return ltrim(str_replace(getScanDir(), '', $filePath), '/\\');
}

function makeReportFromExistHhl($reportFileName, $mhlName)
{
    global $filesProcessed, $invalidFilesCount, $validFilesCount;

    $hFile = fopen(getReportFilePath($reportFileName), 'w');

    $progress = &$filesProcessed[$mhlName];

    fwrite($hFile, "<html>
    <head>
        <style>

            body {
                background-color: #181818;
                font-size: 12px;
                font-family: monospace;
                color: #ababab;
                margin: 5px 3px;
            }

            .caption {
                font-weight: bold;
                background-color: hsl(0deg 0% 20%);
                border-radius: 4px;
                color: #d9d9d9;
                padding: 1px 5px;
                text-shadow: 1px 1px #181818;
            }

            .line {
                padding: 2px 2px 2px 5px;
            }


            .collapsible {
                color: white;
                cursor: pointer;
                width: 100%;
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

        <title>{$mhlName}</title>
    </head>
    <body>\n");

    fwrite($hFile, "\n</div>\n<div class='caption collapsible active' type='button'>Invalid files ({$invalidFilesCount}):</div>\n<div class='content'>\n");

    if (isset($progress['invalid'])) {
        foreach($progress['invalid'] as $filePath => $fileData) {
            $cleanPath = cleanPathForReport($filePath);
            fwrite($hFile, "<div class='line'>{$cleanPath} / valid data: {$fileData['validHashType']}:{$fileData['validHashValue']}</div>\n");
        }
    }

    fwrite($hFile, "\n<div class='caption collapsible active' type='button'>Valid files ({$validFilesCount}):</div>\n<div class='content'>");

    if (isset($progress['valid'])) {
        foreach($progress['valid'] as $filePath => $fileData) {
            $cleanPath = cleanPathForReport($filePath);
            fwrite($hFile, "<div class='line'>{$cleanPath}</div>\n");
        }
    }   

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

