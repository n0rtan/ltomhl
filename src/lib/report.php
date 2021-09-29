<?php

namespace lib\report;

$filesNotInMhl = [];

$filesProcessed = [];

$startTime = time();


function addNotInMhlFile($filePath, $hashType, $hashVal): void
{
    global $filesNotInMhl;

    $filesNotInMhl[$filePath] = [
        $hashType => $hashVal,
    ];
}

function addVerifiedFile($mhl, $filePath): void
{
    global $filesProcessed;

    $filesProcessed[$mhl]['valid'][$filePath] = [];
}

function addInvalidFile($mhl, $filePath): void
{
    global $filesProcessed;

    $filesProcessed[$mhl]['invalid'][$filePath] = [];
}

function makeReport()
{
    global $filesProcessed, $startTime;

    $startDateTime = date('YmdHi', $startTime);

    foreach(array_keys($filesProcessed) as $mhlName) {
        $reportFileName = "{$mhlName}-{$startDateTime}.html";
        makeReportFromExistHhl($reportFileName, $mhlName);
    }
}

function makeReportFromExistHhl($reportFileName, $mhlName)
{
    global $filesProcessed;

    $currentDir = getcwd();
    $reportFilePath = $currentDir . DIRECTORY_SEPARATOR . $reportFileName;

    $hFile = fopen($reportFilePath, 'w');

    $progress = &$filesProcessed[$mhlName];

    fwrite($hFile, "<html>
    <head>
        <style>

            .caption {
                font-size: 18px;
                font-weight: bold;
                background-color: #6814ad;
                color: white;
                padding: 5px;
            }

            .line {
                padding: 3px;
            }

        </style>

        <title>{$mhlName}</title>
    </head>
    <body>\n");

    fwrite($hFile, "<div class='caption'>Valid files:</div>\n");

    if (isset($progress['valid'])) {
        foreach($progress['valid'] as $filePath => $fileData) {
            fwrite($hFile, "<div class='line'>{$filePath}</div>\n");
        }
    }

    fwrite($hFile, "<div class='caption'>Invalid files:</div>\n");

    if (isset($progress['invalid'])) {
        foreach($progress['invalid'] as $filePath => $fileData) {
            fwrite($hFile, "<div class='line'>{$filePath}</div>\n");
        }
    }

    fwrite($hFile, "\n</body>
    </html>");

    fclose($hFile);
}

