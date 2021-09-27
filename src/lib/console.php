<?php

namespace lib\console;

function consoleWriteMessage($message, $newLine = true)
{
    echo $message . ($newLine ? "\n" : '');
}