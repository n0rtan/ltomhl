<?php

function mhl_get_hash($file_path): string
{
    exec("mhl hash -t md5 {$file_path}", $output);

    if (count($output) > 1) {
        throw new Exception("Invalid mhl hash output: \n" . implode("\n", $output));
    }

    return $output[0];
}